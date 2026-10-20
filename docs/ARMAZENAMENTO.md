# Armazenamento e migração segura do É-Pautas

Análise em 20/10/2026, baseada no commit a9f3d5bbd641812e434decace9069457e1e04444.
Não houve acesso ao painel Render nem à base publicada. Não foi constatada perda de
registros; a configuração real do serviço precisa ser confirmada pelo operador.
Esta alteração fica em PR para revisão; não deve ser aplicada automaticamente.

## Evidências e arquitetura

- Laravel 11 (`composer.json`), PHP 8.2, Eloquent. `users` guarda perfis, dados de
  identificação e hashes de senha; `pautas` referencia autores e registra preços,
  aprovação e venda; `compras` referencia usuários/pautas, valores e status.
- O `render.yaml` anterior apontava SQLite para
  `/var/www/html/database/database.sqlite`, sem disco. O commit bb56260 (29/06/2026)
  introduziu a troca. A existência de registros nesse arquivo no servidor é incerta.
- O Dockerfile anterior executava `touch`, `migrate` e `db:seed` no build. Os seeders
  usam `updateOrCreate`: redefinem senhas demonstrativas e `vendida=false` em pautas.
  Não são uma inicialização segura para dados de produção.
- `config/database.php` lê `DB_URL`; a indicação anterior de `DATABASE_URL` no README
  não correspondia à configuração. Apenas mudar driver/URL não transfere registros.
- Sessões guardam autenticação e IDs do carrinho. Antes eram arquivos efêmeros no
  Blueprint; agora sessões e cache usam tabelas já presentes nas migrations.
  `QUEUE_CONNECTION=sync` permanece; não foi introduzido worker/Redis.
- Os controllers de pauta registram somente o nome original do upload em `arquivo`;
  não encontrei gravação do conteúdo por `store`/`move`. PostgreSQL não resolve essa
  lacuna. Faça inventário de `storage/app`, `public` e outros destinos reais antes
  da migração. Arquivos existentes precisam de backup e destino persistente próprio;
  um disco (restrição a uma instância) ou object storage requer decisão separada.
- Migrations usam Schema Builder, foreign keys, decimal(10,2), boolean e enum;
  Laravel suporta SQLite e PostgreSQL. O Docker agora inclui ambos os drivers PDO.
  A compatibilidade depende também de testar dados reais: SQLite pode conter tipos,
  strings longas ou números que PostgreSQL rejeitará. Não arredonde nem descarte
  registros para contornar erros sem revisão.

## Comparação e decisão

Preços públicos consultados em 20/10/2026, em USD/mês, sem impostos, câmbio,
excedentes, arquivos externos ou plano opcional do workspace. Confirme no painel.

| Opção | Custo de referência | Limitações e proteção |
| --- | --- | --- |
| SQLite sem disco | Sem cobrança de disco; web Free possível | Arquivo efêmero. Inadequado para usuários e compras reais. |
| SQLite em disco de 1 GB | Web a partir de US$7 + US$0,25/GB = US$7,25 | Uma instância, um escritor por vez; exige backups consistentes externos. Disco não é backup. |
| PostgreSQL Free | Banco US$0; web cobrado separadamente | 1 GB; expira em 30 dias, mais 14 dias de carência antes da exclusão; sem recuperação gerenciada. Apenas ensaios. |
| PostgreSQL pago | Compute do banco a partir de US$6 + armazenamento US$0,30/GB; web separado | Melhor isolamento do ciclo de deploy, concorrência, integridade e recuperação. PITR: 3 dias no Hobby, 7 dias no Pro+. HA depende de plano adicional. |

Para orçar conservadoramente PostgreSQL de 1 GB com web pago mínimo: US$13,30/mês
(7 + 6 + 0,30). A página anuncia 1 GB incluído: confirme a franquia do plano no painel;
se aplicável, o total é US$13. Não deixe o tamanho padrão ser escolhido sem revisão:
a especificação do Blueprint indica 15 GB como padrão para novos bancos Basic.
Web Free + banco pago reduz o custo, mas tem suspensão por inatividade e limitações
operacionais; não é a recomendação para um marketplace em operação.

**Recomendação:** PostgreSQL gerenciado pago, URL interna na mesma região e backups
externos com restauração ensaiada. É a opção que melhor protege os registros de
usuários e compras no Render pela independência do container, controle de concorrência
e recuperação pontual. Não garante por si só ausência de falhas de lógica ou pagamento.
SQLite persistente é uma alternativa menor e de baixo custo para carga modesta.
Seu disco não pode ser compartilhado com workers/cron/outros serviços, não aparece no
build ou pre-deploy e impede escala horizontal e deploy sem interrupção. Snapshots
são diários, retidos por pelo menos sete dias; não os trate como backup consistente
do banco. Use a API de backup do SQLite ou `.backup` e teste a restauração.

## O que esta alteração implementa sem contratar serviços

- Principal `render.yaml`: PostgreSQL selecionado manualmente por `DB_URL`,
  `APP_KEY` informada pelo operador, SSL obrigatório, sessões/cache em banco;
  `autoDeployTrigger: off`. Não declara `databases`, discos ou plano web novo.
  Em serviço existente, plano omitido preserva o plano atual; em criação nova,
  confira o plano proposto pelo Render antes de confirmar.
- Alternativa `deploy/render.sqlite.yaml`: exemplo **pago**, não aplicado,
  web `0.5c-512mb`, disco `/var/data` de 1 GB. Não sincronize os dois arquivos juntos.
- Docker não cria banco, não migra e não executa seeds. Startup executa
  `storage:verify`, recusa SQLite de produção sem mount real `/var/data` e arquivo
  existente, verifica conexão, foreign keys SQLite, tabelas e migrations pendentes.
  Não cria automaticamente arquivo vazio nem faz fallback para SQLite.
- `storage:import-sqlite`: inspeção por padrão, cópia explícita com `--execute`,
  somente SQLite offline para PostgreSQL vazio com migrations equivalentes.
  A origem é somente leitura; recusa tabelas desconhecidas, violações de FK e
  integridade inválida. Copia todas as tabelas da aplicação (incluindo sessões,
  jobs e cache), preserva IDs/hashes/timestamps, converte booleanos, confere contagens
  e ajusta sequences. Não copia o ledger de migrations; o destino já foi migrado.
  Inserts ocorrem em transação com bloqueio contra escrita concorrente. Se falhar,
  os registros inseridos são revertidos; sequences podem avançar e isso é normal.
- Modo de manutenção estático permite restaurar pelo Shell sem expor o marketplace.
  Nenhuma dessas alterações salva retroativamente um arquivo efêmero perdido.

## 1. Resgate ANTES de redeploy, mudança de plano ou criação de disco

1. No painel Render, confirme serviço, commit em execução, plano, discos, região,
   driver efetivo e destino do banco. Não imprima senha, URL completa ou APP_KEY em
   tickets/logs. Com config cache, confira o valor efetivo de `config('database.default')`
   e do driver via console privado, não apenas as variáveis do painel.
2. Desative auto-deploy no **painel atual**, também. O YAML só muda isso após sync;
   não há proteção retroativa. Suspenda escritores: tráfego de compra/cadastro,
   workers, cron e integrações. `php artisan down` ajuda no app atual; ele não
   substitui bloquear fontes externas de escrita. Não reinicie o container atual.
3. Preserve a APP_KEY atual em cofre de segredos. Trocar a chave invalida sessões e
   pode impedir a leitura de campos criptografados. Não rode `key:generate` na migração.
4. Se SQLite, use o arquivo efetivamente em uso, não uma cópia do repositório. Na
   imagem antiga `sqlite3` CLI pode não existir; a extensão PHP SQLite3 já permite:

   ```sh
   umask 077
   php -r '$s=new SQLite3("/var/www/html/database/database.sqlite", SQLITE3_OPEN_READONLY); $d=new SQLite3("/tmp/epautas-resgate.sqlite"); if (!$s->backup($d)) { exit(1); }'
   php -r '$d=new SQLite3("/tmp/epautas-resgate.sqlite", SQLITE3_OPEN_READONLY); if ($d->querySingle("PRAGMA integrity_check")!=="ok" || $d->querySingle("SELECT COUNT(*) FROM pragma_foreign_key_check")!==0) { exit(1); } echo "Backup íntegro\n";'
   sha256sum /tmp/epautas-resgate.sqlite
   ```

   Adapte o caminho após confirmar a configuração. Backup API inclui dados em WAL;
   não copie só o `.sqlite` aberto, nem remova arquivos `-wal`/`-shm` do banco ativo.
   O destino do backup deve ser um nome novo; não reutilize uma cópia anterior.
5. Transfira imediatamente o backup para fora do container via canal privado
   disponível, como SSH/SCP ou Shell + ferramenta de transferência do Render.
   Confirme hash no computador seguro. `/tmp` também é efêmero. Se o plano não dá
   acesso a Shell/SSH, confirme com o suporte um meio de resgate **antes de mudar
   o plano**: upgrade pode recriar a instância. Não exponha endpoint público de backup.
6. Faça backup de arquivos e inventário de contagens por tabela, IDs, hashes das
   senhas, totais por status das compras, valores em centavos, datas e relações.
   Não publique dados pessoais. Guarde evidências de reconciliação com acesso restrito.
7. Se o banco já for PostgreSQL, não importe SQLite: use `pg_dump --format=custom`
   com cliente compatível, credenciais via arquivo protegido/variáveis privadas;
   restaure em banco vazio com `pg_restore --no-owner --no-acl` e valide. A origem
   confirmada prevalece sobre a configuração antiga do repositório.

## 2. Ensaio isolado de SQLite para PostgreSQL

Use uma cópia do backup, com acesso restrito, e um PostgreSQL descartável. Não conecte
um checkout de testes ao banco publicado. PHP 8.2 com PDO SQLite/PostgreSQL e
`composer install` são necessários; o Dockerfile já os prepara.

1. Defina `APP_KEY` atual, `DB_CONNECTION=pgsql`, `DB_URL` do banco de ensaio,
   `DB_SSLMODE=require` (somente PostgreSQL local sem TLS: `disable`). Remova os
   valores antigos de `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   se não usados; não mantenha `DB_URL` de outro ambiente. Não use `DATABASE_URL`.
2. Rode, no ambiente isolado:

   ```sh
   php artisan config:clear
   php artisan migrate --force
   php artisan storage:import-sqlite /caminho/privado/epautas-resgate.sqlite
   php artisan storage:import-sqlite /caminho/privado/epautas-resgate.sqlite --execute
   php artisan storage:verify
   php artisan migrate:status
   ```

   Trocar `DB_CONNECTION` e rodar `migrate` cria apenas schema, não transfere dados.
   Não use `migrate:fresh`, `db:wipe`, `migrate:refresh`, `--seed` ou `db:seed`.
   Se o ledger do backup for antigo, primeiro aplique migrations **em uma cópia**
   SQLite no ambiente de ensaio, após backup dessa cópia; nunca modifique o resgate
   original. Valide a cópia antes de importar.
3. O importador exige destino vazio (exceto `migrations`); é deliberadamente
   não mesclador e não idempotente. Uma segunda execução recusa sobrescrever dados.
   Erros de schema/tipos/constraints requerem análise na cópia, não exclusão silenciosa.
4. Compare todos os registros entre origem e destino, por PK (ou `email`, `key`,
   `uuid` quando aplicável), coluna a coluna. Normalize apenas booleanos, timezone
   acordado e escala decimal. Valores monetários devem coincidir em centavos,
   não via comparação de float; revise valores fora de duas casas no SQLite.
   Confirme hashes de senha idênticos, IDs e timestamps preservados; nunca rehash
   senhas durante o transporte. Valide ausência de órfãos e duplicidades por regras
   de negócio, além de contagens. O importador checa contagem, não equivalência total.
5. Confira totais de compras por status e por comprador, soma de `valor_pauta`,
   `valor_taxa`, `valor_total`, e a relação `pauta.vendida` versus compras existentes.
   Reconcilie registros financeiros com comprovantes/provedor, se houver.
6. Teste login nos três perfis, criação/moderação de pauta, carrinho, compra e
   consulta administrativa. Crie registros de teste depois da importação para
   testar sequences e IDs sem colisão; faça isso somente no ambiente isolado.
7. Reinicie e redeploy o ambiente de ensaio: os mesmos registros e totais devem
   permanecer. Faça um novo backup PostgreSQL e restaure em outro banco vazio.
   Registre hora, hashes de arquivos, contagens e resultado do ensaio, sem PII pública.

## 3. Corte para PostgreSQL no Render

Somente após o ensaio e decisão do responsável sobre custo/plano:

1. Escolha/crie banco pago, mesma região do web, armazenamento inicial explícito,
   acesso externo restrito e credenciais privadas. Ative alertas de capacidade,
   monitore conexões e confirme a janela de recuperação. Isso depende do painel e
   pode gerar cobrança; este PR não provisiona o recurso.
2. Faça backup final com escritores parados e repita as validações; o backup do
   ensaio pode estar desatualizado. Preserve serviço/arquivo de origem intactos.
3. Após salvar o resgate externamente, configure temporariamente Docker Command
   `sh docker/maintenance.sh` no Render e implante a branch revisada. `/up` retorna
   200 e todas as outras URLs retornam 503, sem processar compras. Este modo permite
   acesso ao Shell para preparar schema e importar sem expor uma base vazia.
4. Configure as variáveis do `render.yaml` com APP_KEY preservada e DB_URL interna.
   Em Blueprint, `sync:false` solicita os valores na criação; em serviço existente
   confirme-os manualmente. Uma atualização de variáveis pode disparar deploy:
   mantenha o comando estático enquanto trabalha. Não sincronize o YAML antes do resgate.
5. No Shell ou one-off job com acesso à origem transferida, execute a sequência de
   migrate, inspeção, importação e verificação da seção 2. Não inicialize seeds.
   Repita reconciliação completa de usuários/pautas/compras e obtenha aceite operacional.
6. Faça backup PostgreSQL pós-importação. Remova o Docker Command temporário para
   voltar ao CMD do Dockerfile, ainda com ingress/escritas bloqueados externamente;
   valide smoke tests e só então reabra tráfego. `storage:verify` falha de modo fechado
   se schema/conexão estiverem incorretos. `/up` sozinho não prova preservação dos dados.
7. Verifique persistência após restart/redeploy controlado e monitore erros e compras.
   Não reative auto-deploy até estabelecer backup e procedimento de migrations por release.

## 4. Alternativa: preservar SQLite com disco

Requer web pago e disco. Faça primeiro o resgate externo e o ensaio. Adicionar disco
provoca deploy e não copia o arquivo antigo. Não monte disco sobre `database/`, pois
isso ocultaria migrations/código da imagem.

1. Aplique apenas `deploy/render.sqlite.yaml`, escolhendo esse caminho no Blueprint,
   com comando temporário `sh docker/maintenance.sh` e APP_KEY original.
2. No Shell da instância com disco, confirme `mountpoint /var/data`, espaço livre e
   transfira o backup para um arquivo temporário dentro do disco. Com app e todos
   os escritores parados, valide `sqlite3 arquivo-temporario 'PRAGMA integrity_check;'
   e `PRAGMA foreign_key_check;`. O primeiro deve ser `ok`, o segundo sem linhas.
3. Se `/var/data/database.sqlite` já existe, faça backup consistente externo dele
   e interrompa: não sobrescreva automaticamente. Para destino novo, instale a cópia
   validada como `/var/data/database.sqlite`, com dono do processo PHP e modo 600;
   diretório deve permitir criação de journal/WAL. Não use chmod 777.
4. Confirme `DB_CONNECTION=sqlite`, `DB_DATABASE=/var/data/database.sqlite`,
   `DB_URL` ausente/vazia e `DB_FOREIGN_KEYS=true`. URL residual pode sobrepor o driver.
   Rode `php artisan config:clear`, `php artisan migrate --force` se necessário e
   `php artisan storage:verify`; nunca seed. Execute migrations no runtime: disco
   não está disponível durante build/pre-deploy/one-off jobs.
5. Remova o comando temporário e valide fluxos, dados e persistência após restart
   e redeploy, antes de reabrir tráfego. Uma única instância atende todas as escritas.
6. Programe backup consistente via `.backup` ou API SQLite em processo que acesse
   o disco e envie o resultado para fora do serviço. Cron separado no Render não
   monta esse disco. Defina retenção, criptografia, alertas de falha/espaço e ensaie
   restauração. Escolha frequência conforme a perda máxima de compras aceitável.

## 5. Rollback e continuidade

- Antes da reabertura, se houver falha: mantenha manutenção, conserve ambos os
  bancos, reverta código/configuração para a origem preservada e valide novamente.
  Se o SQLite antigo era efêmero, não presuma que sobreviveu: restaure o resgate em
  armazenamento durável. Nunca volte ao caminho efêmero para aceitar compras reais.
- Depois de aceitar novas escritas, voltar ao backup antigo descartaria essas
  compras. Congele operações, faça backups dos dois lados e reconcilie o delta em
  cópia isolada. Reabrir exige reconciliar todos os novos IDs, valores e pagamentos.
- Reverter código não deve desfazer automaticamente migrations. Migrations futuras
  precisam de compatibilidade com a versão anterior e plano próprio de rollback.
- Meta sugerida a ser aprovada pelo responsável: backups externos ao menos diários,
  mais frequentes se a perda aceitável for menor; ensaio de recuperação mensal,
  alerta de falhas imediato. PITR complementa, não substitui retenção externa.
- Persistência não corrige regras de negócio: o checkout atual cria compra e marca
  pauta vendida em operações separadas, sem transação global/controle de concorrência;
  também atribui `paga` sem confirmação de provedor nesse fluxo. Exclusões em cascata
  podem apagar compras ao excluir usuário/pauta. São riscos adicionais que precisam
  de uma revisão própria; não altere sem decidir regras de venda e retenção.

## Validação técnica

`tests/Feature/StorageVerificationTest.php` verifica recusa de base vazia, preservação
de registros após reconexão e bloqueio de SQLite efêmero em produção. O workflow
`.github/workflows/storage.yml` executa essas verificações e migrations PostgreSQL
16 com usuários/pautas/compras, booleanos e valores decimais. Os testes usam somente
bases descartáveis; não substituem ensaio com backup real nem validam a conta Render.

## Referências oficiais

Consultadas em 20/10/2026:

- https://render.com/pricing
- https://render.com/docs/disks
- https://render.com/docs/free
- https://render.com/docs/postgresql-backups
- https://render.com/docs/blueprint-spec
- https://laravel.com/docs/11.x/database
- https://laravel.com/docs/11.x/migrations
