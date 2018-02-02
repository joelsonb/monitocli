# Create objects
create all
Cria todos os objetos de todas as tabelas do banco de dados da conexão padrão

dto | dao | model
Cria somente os arquivos de um tipo

Opções:
```bash
-c, --connection | padrão: conexão padrão
Nome da conexão de onde serão lidas as tabelas

--from-file
Arquivo de onde será lida a lista de tabelas e colunas

--to-file
Arquivo de destino das configurações

-t, --table
Nome ou lista de nomes, separados por vírgulas, das tabelas que serão lidas

--column
Nome ou lista de nomes, separados por vírgulas, das colunas que serão lidas

-n, --namespace
Namespace dos arquivo criados
```