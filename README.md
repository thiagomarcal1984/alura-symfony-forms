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
