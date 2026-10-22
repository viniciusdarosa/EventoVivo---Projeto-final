# Dumps legados (histórico) — NÃO usar em bancos com dados reais

Todos os arquivos `.sql` desta pasta são os dumps originais do
banco EventoVivo (20/08/2026, mais o dump de `carrossel_fotos` de
08/10/2026). Foram mantidos apenas como referência histórica.

**Cada um deles contém `DROP TABLE IF EXISTS`** — importá-los em um
banco que já tem dados **apaga as tabelas** e recria com os dados do
dump.

Para instalação nova use a pasta `../instalacao/` (00 → 01 → 02).
Para banco existente use `../instalacao/03_migracao_banco_existente.sql`.

Inconsistência conhecida destes dumps (corrigida nos dados novos):
o dump de `freelancers` inseria um freelancer de teste com
`usuario_id = 6` sem o correspondente usuário no dump de `usuario`.
