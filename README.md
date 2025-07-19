# Kamishibai

Aplicação Laravel para registro e análise de adesão a pacotes de cuidados
(Kamishibai). Gestores podem cadastrar categorias (por exemplo, *"ADESÃO
MENSAL AO PACOTE DE CUIDADOS DE CATETER VESICAL DE DEMORA (CVD)"*) e os
itens de verificação que compõem cada categoria. Usuários marcam diariamente
se cada item foi cumprido (**C**) ou não (**NC**), possibilitando geração de
relatórios mensais e anuais.

O projeto foi inicializado com Laravel 12 e contém migrations e models para:

* **categories** – grupos de verificação cadastrados pelo gestor;
* **items** – itens pertencentes às categorias;
* **records** – registros diários de conformidade;
* **users** – com coluna `role` para diferenciar `nurse` e `manager`.
