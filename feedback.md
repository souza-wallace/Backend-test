# Feedback
Esse documento visa coletar feedbacks sobre o teste de desenvolvimento. Desde o início do teste até a entrega do projeto.

## Antes de Iniciar o Teste

1 - Fale sobre suas primeiras impressões do Teste:
> Achei o teste coeso, pois avalia habilidades nescessarias e rotineiras da função, criação de Api's, testes, regras especificas para tratar exceções na lógica.

2 - Tempo estimado para o teste:
> O prazo de realização eram 2 dias uteis, a partir do recebimento. 1 dia.

3 - Qual parte você no momento considera mais difícil?
> Escrever testes, pois em minha experiencias profissionais as empresas em que trabalhei não tinham a prática de escrever testes, então o conheceimento que tenho é basico, e foi adquirido por conta própia estudando pelos conteúdos disponíveis na internet.

4 - Qual parte você no momento considera que levará mais tempo?
> Escrever os testes

5 - Por onde você pretende começar?
> Criando os endpoints da API


## Após o Teste

1 - O que você achou do teste?
> Desafiador, de primeira impressão me senti confiante para realizar o teste, pensei que fosse fazer com mais tranquilidade porém tive alguns imprevistos que me ocuparam bastante tempo como vou descrever abaixo.

2 - Levou mais ou menos tempo do que você esperava?
> Sim, pensei que iria fazer em 1 dia. e acabei usando 1 dia e meio

3 - Teve imprevistos? Quais?
> Proteção dos ids, achei que esse tópico seria fácil, porém  tive alguns impecilios:
1- Migração - duas chaves prímarias na tabela, o laravel nao cria um tipo inteiro incrementavel sem ser 'constraint', todos os metodos(increments, unsignedBigInteger, id ...) adicionam uma 'constraint' na coluna id e isso conflitava com a coluna code.
mudei a coluna code para unique ao inves de primary e resolvi esse primeiro problema, pois agora era possivel rodar as migrações sem obter erros.

2- Para que o laravel reconhecesse o campo code como parametro a ser utilizado nas urls e relacionamentos, modifiquei o model adicionando $primaryKey = "code", porem ao fazer essa mudança teria que mudar a migracao, pois tinha definido um relacionamento entre as tabelas, e como tive problemas na migracao ja sabia que nao iria conseguir manter o relacionamento usando o eloquent, entao adicionei uma coluna code na tabela de logs e fiz a busca dos registros usando um where mesmo, ao inves de usar o metodo de relacionamento do model.

4 - Existem pontos que você gostaria de ter melhorado?
> Sim,gostaria de ter desenvolvido todos os testes pedidos.

5 - Quais falhas você encontrou na estrutura do projeto?
> não encontrei nenhuma falha.