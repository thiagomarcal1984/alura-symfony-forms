# Symfony Flex
Existem dois tipos de instalação do Symfony: 
1. Fullstack completa; e
2. Mínima.

Se você estiver usando a instalação mínima do Symfony, você pode usar instalações mais simplificadas através do componente Symfony Flex. Usar o Symfony Flex pode ser incompatível com o pacote symfony/symfony padrão. Consulte a documentação: https://symfony.com/doc/current/setup/flex.html 

Depois que o Symfony Flex é instalado (e o problema do conflito com o pack Symfony padrão), ele acrescenta mais poderes ao composer (podemos usar o composer da seguinte forma):

```
composer require <componente1> <componente2> ...
```

Exemplo: 
```
composer require annotations asset orm twig logger mailer form security translation validator
```

Tudo que for importado usando o Symfony Flex é referenciado no arquivo `config\bundles.php`. O código nesse arquivo para a instalação mínima seria:

```php
<?php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    // Para cada classe, um array do tipo ['ambiente' => true].
    // Exemplos de ambiente: dev, test, prod, all.
];
```

Cada bundle referenciado no arquivo `config\bundles.php` contém elementos próprios (por exemplo, configurações, views, controllers)

Aquela página inicial do Symfony que muda de cor a cada refresh é gerada automaticamente pelo componente FrameworkBundle, aquele padrão da instalação mínima!

O arquivo `src\Kernel.php` inicializa todo o framework, e ele utiliza o arquivo `config\bundles.php`.

# Debug toolbar
A debug toolbar já vem por padrão com o Twig. Mas ela pode ser instalada isoladamente, com a dependência `symfony/profiler-pack`: https://symfony.com/doc/current/profiler.html

Pode ser melhor usar o Symfony Profiler/Debug toolbar do que as ferramentas de desenvolvedor do navegador. A Debug toolbar mostra dados de sessão, flash messages, ordem de carregamento dos templates do Twig, funcionamento do cache, parâmetros de configuração da aplicação etc.

# Nomeando rotas
Por padrão, nomeamos as rotas prefixando-as com `app_`.

Código PHP do controller:
```php
    #[Route('/series/create', name: 'app_series_form', methods: ['GET'])]
    public function addSeriesForm() : Response {
        return $this->render('/series/form.html.twig');
    }
```

A função `path` converte o nome da rota para o caminho real da rota. Assim, o caminho pode ser modificado porque ele é recuperado a partir do nome da rota.

Código para invocar a rota nomeada no Twig:
```php
    <a class="btn btn-dark mb-3" href="{{ path('app_series_form') }}">Adicionar</a>
```

Comando na CLI para mostrar todas as rotas da aplicação: 
```
php .\bin\console debug:router
```

Caso você também queira ver qual o controller executado em cada rota, pode adicionar o parâmetro `--show-controllers` ao comando, ficando:
```
 php bin/console debug:router --show-controllers
```

# Botão de exclusão
A boa prática é que ações que **modifiquem** o banco de dados sejam invocadas pelo método `POST`, não pelo método `GET`.

Exemplo: se os webcrawlers identificarem alguma URL que permita modificações do banco através do método GET, os webcrawlers vão acidentalmente alterar o banco de dados.
