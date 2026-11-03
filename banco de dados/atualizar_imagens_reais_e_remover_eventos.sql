-- Atualiza eventos já importados usando as imagens reais que estão em uploads/eventos/.
-- Também remove os eventos solicitados; os favoritos relacionados são removidos pela FK ON DELETE CASCADE.
-- Execute no schema eventovivo. A coluna imagem_capa guarda apenas o nome do arquivo.
USE `eventovivo`;

UPDATE `eventos` SET `imagem_capa` = 'evento_cultura_festival_chines.jpg' WHERE BINARY `titulo` = BINARY '3º Festival Cultural Chinês de Santa Catarina';
UPDATE `eventos` SET `imagem_capa` = 'evento_cultura_haru_matsuri.jpg' WHERE BINARY `titulo` = BINARY '4ª edição do Haru Matsuri';
UPDATE `eventos` SET `imagem_capa` = 'evento_cultura_feira_ceramica.jpg' WHERE BINARY `titulo` = BINARY '8ª Feira de Cerâmica de Florianópolis';
UPDATE `eventos` SET `imagem_capa` = 'evento_cultura_ze_paiva.jpg' WHERE BINARY `titulo` = BINARY 'Exposição A Reinvenção da Natureza — Zé Paiva';
UPDATE `eventos` SET `imagem_capa` = 'evento_cultura_natal_pomerode.png' WHERE BINARY `titulo` = BINARY 'Natal em Pomerode 2026';
UPDATE `eventos` SET `imagem_capa` = 'evento_danca_danca_das_flores.jpeg' WHERE BINARY `titulo` = BINARY 'A Dança das Flores';
UPDATE `eventos` SET `imagem_capa` = 'evento_danca_historia_movimento.png' WHERE BINARY `titulo` = BINARY 'Dança — Uma História em Movimento';
UPDATE `eventos` SET `imagem_capa` = 'evento_danca_cartas_ao_mar.png' WHERE BINARY `titulo` = BINARY 'Cartas ao Mar — XVII Mostra de Dança do Casarão da Dança';
UPDATE `eventos` SET `imagem_capa` = 'evento_danca_anos_2000.jpg' WHERE BINARY `titulo` = BINARY 'Anos 2000 — espetáculo de fim de ano da Central da Dança';
UPDATE `eventos` SET `imagem_capa` = 'evento_festival_festa_pomerana.jpg' WHERE BINARY `titulo` = BINARY '42ª Festa Pomerana';
UPDATE `eventos` SET `imagem_capa` = 'evento_teatro_animaneco.jpg' WHERE BINARY `titulo` = BINARY 'Elisa e os Cisnes Selvagens — 8º Animaneco';
UPDATE `eventos` SET `imagem_capa` = 'evento_teatro_pintor_palavra.png' WHERE BINARY `titulo` = BINARY 'Pintor da Palavra — 8º Animaneco';
UPDATE `eventos` SET `imagem_capa` = 'evento_teatro_mulher_passaro.jpg' WHERE BINARY `titulo` = BINARY 'Mulher Pássaro — 8º Animaneco';

DELETE FROM `eventos`
WHERE BINARY `titulo` IN (BINARY 'Em Cena — espetáculo de dança', BINARY 'Dois de Nós');
