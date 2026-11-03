-- Corrige as capas repetidas nos eventos complementares já importados.
-- Os nomes são resolvidos pela aplicação em uploads/eventos/.
-- Execute uma vez no schema eventovivo; os UPDATEs são seguros para reexecutar.
USE `eventovivo`;

UPDATE `eventos` SET `imagem_capa` = CASE BINARY `titulo`
  WHEN BINARY '3º Festival Cultural Chinês de Santa Catarina' THEN 'evento_cultura_festival_chines.svg'
  WHEN BINARY '4ª edição do Haru Matsuri' THEN 'evento_cultura_haru_matsuri.svg'
  WHEN BINARY '8ª Feira de Cerâmica de Florianópolis' THEN 'evento_cultura_feira_ceramica.svg'
  WHEN BINARY 'A Dança das Flores' THEN 'evento_danca_danca_das_flores.svg'
  WHEN BINARY 'Dança — Uma História em Movimento' THEN 'evento_danca_historia_movimento.svg'
  WHEN BINARY 'Em Cena — espetáculo de dança' THEN 'evento_danca_em_cena.svg'
  WHEN BINARY 'Cartas ao Mar — XVII Mostra de Dança do Casarão da Dança' THEN 'evento_danca_cartas_ao_mar.svg'
  WHEN BINARY 'Anos 2000 — espetáculo de fim de ano da Central da Dança' THEN 'evento_danca_anos_2000.svg'
  WHEN BINARY '42ª Festa Pomerana' THEN 'evento_festival_festa_pomerana.svg'
  WHEN BINARY 'Elisa e os Cisnes Selvagens — 8º Animaneco' THEN 'evento_teatro_elisa_cisnes.svg'
  WHEN BINARY 'Pintor da Palavra — 8º Animaneco' THEN 'evento_teatro_pintor_palavra.svg'
  WHEN BINARY 'Dois de Nós' THEN 'evento_teatro_dois_de_nos.svg'
  ELSE `imagem_capa`
END
WHERE BINARY `titulo` IN (
  '3º Festival Cultural Chinês de Santa Catarina',
  '4ª edição do Haru Matsuri',
  '8ª Feira de Cerâmica de Florianópolis',
  'A Dança das Flores',
  'Dança — Uma História em Movimento',
  'Em Cena — espetáculo de dança',
  'Cartas ao Mar — XVII Mostra de Dança do Casarão da Dança',
  'Anos 2000 — espetáculo de fim de ano da Central da Dança',
  '42ª Festa Pomerana',
  'Elisa e os Cisnes Selvagens — 8º Animaneco',
  'Pintor da Palavra — 8º Animaneco',
  'Dois de Nós'
);
