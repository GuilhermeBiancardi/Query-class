# QueryPdo Class

Classe PHP wrapper para conexões seguras e fáceis com banco de dados MySQL utilizando PDO.

## Inclusão da Classe

```php
include_once "diretorio/QueryPdo.class.php";

use Biancardi\Database\QueryPdo;
```

## Configuração

A classe utiliza o padrão **Singleton** para gerenciar a conexão. Você pode configurar a conexão de duas formas:

### 1. Definindo Constantes (Recomendado)

Defina as constantes `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASS` antes de chamar a classe.

```php
define("DB_HOST", "localhost");
define("DB_NAME", "nome_do_banco");
define("DB_USER", "usuario");
define("DB_PASS", "senha");

$db = QueryPdo::getInstance();
```

### 2. Passando Parâmetros na Primeira Chamada

Você pode passar os dados de conexão diretamente no método `getInstance` na primeira vez que ele for chamado.

```php
$db = QueryPdo::getInstance(
    "mysql:host=localhost;dbname=nome_do_banco;charset=utf8mb4",
    "usuario",
    "senha"
);
```

---

## Modo de Uso

### Selecionando Dados (`select`)

O método `select` retorna um array de arrays associativos ou `false` se não houver resultados.

```php
$db = QueryPdo::getInstance();

// Exemplo básico
$sql = "SELECT * FROM usuarios";
$usuarios = $db->select($sql);

// Exemplo com parâmetros (Prepared Statements)
$sql = "SELECT * FROM usuarios WHERE id = :id";
$params = [':id' => 1];
$usuario = $db->select($sql, $params);

if ($usuario) {
    print_r($usuario);
}
```

### Inserindo Dados (`insert`)

O método `insert` retorna o ID do último registro inserido.

```php
$sql = "INSERT INTO usuarios (nome, sobrenome) VALUES (:nome, :sobrenome)";
$params = [
    ':nome' => 'Guilherme',
    ':sobrenome' => 'Biancardi'
];

$novoId = $db->insert($sql, $params);
echo "Usuário criado com ID: " . $novoId;
```

### Atualizando Dados (`update`)

O método `update` retorna `true` em caso de sucesso.

```php
$sql = "UPDATE usuarios SET nome = :nome WHERE id = :id";
$params = [
    ':nome' => 'Novo Nome',
    ':id' => 1
];

if ($db->update($sql, $params)) {
    echo "Atualizado com sucesso!";
}
```

### Deletando Dados (`delete`)

Funciona de forma similar ao update.

```php
$sql = "DELETE FROM usuarios WHERE id = :id";
$params = [':id' => 1];

$db->delete($sql, $params);
```

## Transações

A classe suporta transações manuais (beginTransaction, commit, rollback).

```php
try {
    $db->beginTransaction();

    $db->insert("INSERT INTOLog ...", [...]);
    $db->update("UPDATE Conta ...", [...]);

    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    echo "Erro na transação: " . $e->getMessage();
}
```

## Segurança

A classe `QueryPdo` utiliza **Prepared Statements** do PDO internamente em todos os métodos (`execute`, `query`, `select`, `insert`, etc.). Isso previne automaticamente ataques de SQL Injection, não sendo necessário sanitizar as strings manualmente como na classe antiga.

Basta sempre passar os valores dinâmicos array de `$params`.

```php
// SEGURO
$db->select("SELECT * FROM users WHERE name = :name", [':name' => $inputUsuario]);

// INSEGURO (Não faça isso)
$db->select("SELECT * FROM users WHERE name = '$inputUsuario'");
```

## Tratamento de Erros e Logs

Erros de conexão ou execução geram logs no `error_log` do PHP com o prefixo `[QueryPdo Error]` e lançam uma `Exception` que pode ser capturada.

```php
try {
    $db->select("SELECT * FROM tabela_inexistente");
} catch (Exception $e) {
    echo "Ocorreu um erro: " . $e->getMessage();
}
```
