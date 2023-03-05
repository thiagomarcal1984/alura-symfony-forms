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

# Excluindo uma série
Lembre-se do padrão POST-REDIRECT-GET: impeça o reenvio de formulário.

Código para excluir um objeto:
```php
class SeriesController extends AbstractController
{
    public function __construct(
        private SeriesRepository $seriesRepository,
        // EntityManager foi injetado para RECUPERAR o objeto.
        private EntityManagerInterface $entityManager, 
    )
    {
    }
 
    #[Route('/series/delete', methods: ['POST'])]
    public function deleteSeries(Request $request) : Response {
        $id = $request->query->get('id');
        // getPartialReference recupera o objeto que conterá apenas o ID.
        $series = $this->entityManager->getPartialReference(Series::class, $id);
        $this->seriesRepository->remove($series, flush: true);
        return new RedirectResponse('/series');
    }
}
```

# Parâmetros em rotas
 Inserindo lógica de remoção por ID no repositório: 
 ```php
 ...
class SeriesRepository extends ServiceEntityRepository
{
    ...
     public function removeById(int $id) {
        // getPartialReference recupera o objeto que conterá apenas o ID.
        $series = $this->getEntityManager()->getPartialReference(Series::class, $id);
        $this->remove($series, flush: true);
    }
}
```

 Removendo lógica de remoção por ID no controlador, removendo o EntityManager, inserindo nome da rota e mudando método HTTP permitido: 
 ```php
 ...
class SeriesController extends AbstractController
{
    public function __construct(private SeriesRepository $seriesRepository)
    {
    }
    ...
     #[Route('/series/delete/{id}', name: 'app_delete_series', methods: ['DELETE'])]
    public function deleteSeries(int $id) : Response {
        $this->seriesRepository->removeById($id);
        return new RedirectResponse('/series');
    }
}
```

É possível forçar no Symfony que os formulários HTML usem outros métodos HTTP além do `GET` e `POST` através de uma configuração em `\config\packages\framework.yaml`:

```YML
framework:
    ...
    http_method_override: true
```

O parâmetro `http_method_override` determina se o parâmetro de requisição `_method` é usado como o método HTTP pretendido nas requisições do tipo POST:
```HTML
<form action="{{ path('app_delete_series', { id: series.id }) }}" method="post">
    <input type="hidden" name="_method" value="DELETE">
    <button class="btn btn-sm btn-danger">
        X
    </button>
</form>
```

# Injeção de dependências
O parâmetro `requirements` dentro da anotação `Route` permite definir um array associativo que estabelece regras para cada parâmetro fornecido no path.

Exemplo de código usando a entidade:
```php
    #[Route(
        '/series/delete/{series}', 
        name: 'app_delete_series', 
        methods: ['DELETE'],
        // O Symfony vai varrer a classe entidade até achar a 'id', depois ele recupera a entidade.
        requirements : ['id' => '[0-9]+'], 
    )]
    public function deleteSeries(Series $series) : Response {
        $this->seriesRepository->remove($series, flush: true);
        return new RedirectResponse('/series');
    }
```
HTML usado para chamar a rota `app_delete_series`:
```HTML
<form action="{{ path('app_delete_series', { series: series.id }) }}" method="post">
    ...
</form>
```


Agora exemplo de código usando o id da entidade diretamente:
```php
    #[Route(
        '/series/delete/{id}', 
        name: 'app_delete_series', 
        methods: ['DELETE'],
        // O Symfony vai varrer a classe entidade até achar a 'id', depois ele recupera a entidade.
        requirements : ['id' => '[0-9]+'], 
    )]
    public function deleteSeries(int $id) : Response {
        $this->seriesRepository->removeById($id);
        return new RedirectResponse('/series');
    }
```

O HTML não vai receber mais o parâmetro `series` na função `path`, mas sim o parâmetro `id`:
```HTML
<form action="{{ path('app_delete_series', { id: series.id }) }}" method="post">
    ...
</form>
```

Sobre injeção de dependência: é possível instruir o Symfony a como criar determinados objetos. Para isso, é necessário referenciar no arquivo `/config/services.yaml` as classes e diretórios que os contém(?).

# Adicionando mensagem
Fazendo flash messages na unha usando sessões:

Na função `deleteSeries`, inserir na sessão a mensagem confirmando a remoção:
```php
    public function deleteSeries(int $id, Request $request) : Response {
        $this->seriesRepository->removeById($id);
        $session = $request->getSession();
        $session->set('success', 'Série removida com sucesso.');
        return new RedirectResponse('/series');
    }
```
Na função `addSeries`, inserir na sessão a mensagem confirmando a inserção:
```php
    #[Route('/series/create', name: 'app_add_series', methods: ['POST'])]
    public function addSeries(Request $request) : Response {
        $seriesName = $request->request->get('name');
        $series = new Series($seriesName);
        $session = $request->getSession();
        $session->set('success', "Série \"$seriesName\" incluída com sucesso.");
        $this->seriesRepository->save($series, true);
        return new RedirectResponse('/series');
    }
```

Na função `index`, exibir a mensagem e removê-la da sessão: 
```php
    public function index(Request $request): Response
    {
        $seriesList = $this->seriesRepository->findAll();
        $session = $request->getSession();
        $successMessage = $session->get('success');
        $session->remove('success');
        $seriesList =  $this->seriesRepository->findAll();

        return $this->render('series/index.html.twig', [
            'seriesList' => $seriesList,
            'successMessage' => $successMessage,
        ]);
    }

```

# Editando uma série
Nova rota para edição:
```php
    #[Route('/series/edit/{series}', name: 'app_edit_series_form', methods: ['GET'])]
    public function editSeriesForm(Series $series): Response {
        return $this->render('series/form.html.twig', compact('series'));
    }
```

Repare que a função `compact` recebe como parâmetro uma `string` que representa o nome da variável que contém o array, não a `entidade` $series. Ela cria um array associativo cujos índices são os nomes das variáveis, e os valores são os contéudos de cada variável:

```php
<?php
$cidade = "Sao Paulo";
$estado = "SP";
$evento = "SIGGRAPH";

$vars_localidade = array("cidade", "estado");

$result = compact("evento", $vars_localidade);
print_r($result);
?>
```
Cuja saída é:
```
Array
(
    [evento] => SIGGRAPH
    [cidade] => Sao Paulo
    [estado] => SP
)
```

Atualização em `form.html.twig` (isso vai funcionar para edição, mas vai quebrar para inserção):
```HTML
<input class="form-control" type="text" name="name" id="name" value="{{ series.name }}">
```

Inserção do botão de editar em `index.html.twig`:
```HTML
<ul class="list-group">
    {% for series in seriesList %}
        <li class="list-group-item d-flex justify-content-between align-items-center">
            {{ series.name }}
            <div class="d-flex">
                <a href="{{ path('app_edit_series_form', { series: series.id }) }}" class="btn btn-sm btn-primary me-2">
                    E
                </a>
                <form action="{{ path('app_delete_series', { id: series.id }) }}" method="post">
                    <input type="hidden" name="_method" value="DELETE">
                    <button class="btn btn-sm btn-danger">
                        X
                    </button>
                </form>
            </div>
        </li>
    {% endfor %}
</ul>
```

# Salvando a edição
Nova rota para confirmar as alterações na entidade:
```php
class SeriesController extends AbstractController
{
    public function __construct(
        private SeriesRepository $seriesRepository,
        // Praticando a injeção da dependência do EntityManager.
        private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/series/edit/{series}', name: 'app_store_series_changes', methods: ['PATCH'])]
    public function storeSeriesChanges(Series $series, Request $request): Response {
        $series->setName($request->request->get('name'));
        $this->entityManager->flush(); // Confirma as alterações no banco.
        $request->getSession()->set('success', "Série {$series->getName()} editada com sucesso");
        return new RedirectResponse('/series');
    }
}
```

Formas diferentes de testar conteúdo de uma variável no Twig:
```
    {# Se precisar apenas verificar se está vazia, use: #}
    {% if successMessage is not empty %}

    {# Se precisar comparar o conteúdo, use esta: #}
    {% if successMessage is not same as '' %} 
```

Adaptação do arquivo `form.html.twig`:
```php
{% block title %}{{ series is defined ? 'Editar' : 'Nova' }} Série{% endblock %}

{% block body %}
    <form method="post" action="{{ series is defined ? path('app_store_series_changes', { series: series.id }) : path('app_add_series') }}">
        <div class="mb-3">
            <label class="form-label" for="name">Nome da série</label>
            <input class="form-control" type="text" name="name" id="name" value="{{ series is defined ? series.name : '' }}">
        </div>

        {% if series is defined %}
            <input type="hidden" name="_method" value="PATCH">
        {% endif %}

        <button class="btn btn-dark">
            {{ series is defined ? 'Editar' : 'Adicionar' }}
        </button>
    </form>
{% endblock %}
```

Atenção aos parâmetros na função `path`. Os parâmetros para enviar para o controlador é `series.id`, não a entidade toda:
```php
path('app_store_series_changes', { series: series.id }) 
```

# Usando Flash Message
Sintaxe de flash messages no controller:
```php
$this->addFlash('success', 'Série removida com sucesso.');
```
Sintaxe para exibir as flash messages no template:
```php
{# pass an array argument to get the messages of those types  #}
{% for type, messages in app.flashes(['success', 'sucess']) %}
    {% for message in messages %}
        <div class="alert alert-{{ type }}">
            {{ message }}
        </div>
    {% endfor %}
{% endfor %}
```

O objeto `app` sempre existe em um template do Twig. Pode referenciá-lo sem medo.

# Entendendo a ideia
O Symfony dispõe de um componentes para formulários, que pode ser baixado por meio do comando abaixo (caso você não esteja usando a versão completa do Symfony):

```
composer require symfony/form
```


Em `config/packages/twig.yaml` nós podemos definir o tema para os formulários. Depois de realizados os imports, podemos modificar esse arquivo e incluir o tema do Bootstrap 5:
```yaml
twig:
    form_themes: ['bootstrap_5_layout.html.twig']
```
Você também pode aplicar o tema localmente no template ao invés de fazer isso globalmente em `config/packages/twig.yaml`:
```php
{% form_theme form 'bootstrap_5_layout.html.twig' %}
```

O método para criação do formulário no controller se chama `createFormBuilder`. Ele retorna um objeto do tipo `FormBuilderInterface`.

Para cada campo do formulário você usa o método `add` que tem 3 parâmetros: nome do campo, classe do campo e um array com opções.

Finalmente, o `FormBuilderInterface` não pode ser enviado para o template, mas sim o formulário gerado pelo método `getForm()`.

Veja o controlador abaixo:
```php
    #[Route('/series/create', name: 'app_series_form', methods: ['GET'])]
    public function addSeriesForm() : Response {
        $seriesForm = $this->createFormBuilder(new Series(''))
            ->add('name', TextType::class, [ 'label' => 'Nome' ])
            ->add('save', SubmitType::class, [ 'label' => 'Adicionar' ])
            ->getForm()
        ;

        return $this->renderForm('/series/form.html.twig', compact('seriesForm'));
    }
```


O método para renderização de uma página com um formulário do Symfony é diferente. Não é `render`, mas sim `renderForm`:

```php
return $this->renderForm('/series/form.html.twig', compact('seriesForm'));
```

Finalmente, para renderizar o formulário Symfony no Twig use a função `form` e forneça como parâmetro o formulário que está no controller:

```php
{% block body %}
    {{ form(seriesForm) }}
{% endblock %}

```
# Extraindo um FormType
No contexto de formulários, o Symfony pode organizar os objetos em 3 classes: 
1. um tipo para os campos individuais (TextType, SubmitType etc.);
2. um tipo para grupos de campos (Endereço, por exemplo); e
3. um tipo para o formulário inteiro.

Criando um formulário via linha de comando do Symfony:
```
php bin/console make:form EntidadeType.
```

Exemplo do comando usado para o formulário SeriesType:
```
PS D:\alura\symfony-forms\controle_series_symfony> php bin/console make:form SeriesType

 The name of Entity or fully qualified model class name that the new form will be bound to (empty for none):
 > Series

 created: src/Form/SeriesType.php

 
  Success! 
 

 Next: Add fields to your form and start using it.
 Find the documentation at https://symfony.com/doc/current/forms.html
 ```

 Código do `SeriesType`:
 ```php
namespace App\Form;

// ...

class SeriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // As opções do campo name não foram geradas automaticamente.
            ->add(child: 'name', options: [ 'label' => 'Nome' ])
            // O campo save não foi gerado automaticamente.
            ->add('save', SubmitType::class, [ 'label' => 'Adicionar' ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Series::class,
        ]);
    }
}
 ```

 Código da rota para adicionar uma série:
 ```php
     #[Route('/series/create', name: 'app_series_form', methods: ['GET'])]
    public function addSeriesForm() : Response {
        $seriesForm = $this->createForm(SeriesType::class, new Series(''));
        return $this->renderForm('/series/form.html.twig', compact('seriesForm'));
    }
 ```

Repare que o método `createForm` tem dois parâmetros: o tipo de formulário e o objeto que será representado no formulário.

**O código para inserção via POST ficou quebrado**.

# Lidando com o envio de dados

Código da rota de criação da série:
```php
    #[Route('/series/create', name: 'app_add_series', methods: ['POST'])]
    public function addSeries(Request $request) : Response {
        $series = new Series();
        $this->createForm(SeriesType::class, $series)
            ->handleRequest($request) // Preenche o objeto $series com os dados da requisição.
            // ->isValid() // booleano.
            // ->isSubmitted() // booleano.
            // ->getData() // Retorna o objeto $series preenchido.
        ;
        $this->seriesRepository->save($series, true);
        $this->addFlash(
            'success', 
            "Série \"{$series->getName()}\" incluída com sucesso."
        );
        return new RedirectResponse('/series');
    }
```

O método `handleRequest` no formulário exige uma requisição como parâmetro. Isso é óbvio, mas não se esqueça.

O `handleRequest` pega os parâmetros da requisição e tenta inseri-los no objeto fornecido como segundo parâmetro do método `createForm`. Ou seja, o objeto `$series` vai ser o mesmo (inclusive ter a mesma identificação) daquele que for retornado do método `$this->createForm()->handleRequest()->getData()`.

**Agora o código para inserção via POST não está mais quebrado.**

# Adicionando validações
Uma das formas de fazer validação no Symfony é colocando constraints nas propriedades das entidades. Essas constraints podem ser aplicadas através do código disponível no namespace `Symfony\Component\Validator\Constraints`.

Assim, os formulários do Symfony conseguem configurar o seu frontend para definir as validações client-side.

Código da entidade Series:
```php
namespace App\Entity;

use App\Repository\SeriesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SeriesRepository::class)]
class Series
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column]
        #[Assert\NotBlank]
        #[Assert\Length(min: 5)]
        private ?string $name = ''
    )
    {
    }

    // Resto do código.
}
```

# Validando os dados
O componente de validação do Symfony não vem na instalação mínima. Para usá-lo, instale com o composer:
```
composer require symfony/validator
```
Uma vez que você tem o componente instalado e define os formulários da forma vista anteriormente, você testa a validação do formulário usando o método `isValid()` do formulário. Caso o formulário esteja inválido, o controller retorna o template com o formulário inválido mas preenchido:
```php
    #[Route('/series/create', name: 'app_add_series', methods: ['POST'])]
    public function addSeries(Request $request) : Response {
        $series = new Series();
        $seriesForm = $this->createForm(SeriesType::class, $series)
        ;
        if(!$seriesForm->isValid()) {
            return $this->renderForm('series/form.html.twig', compact('seriesForm'));
        }
        $this->seriesRepository->save($series, true);
        $this->addFlash(
            'success', 
            "Série \"{$series->getName()}\" incluída com sucesso."
        );
        return new RedirectResponse('/series');
    }
```
Adendo: as constraints na entidade podem deixar o código mais poluído e menos flexível para mudanças de framework. Você pode definir as validações no arquivo `config\validator\validation.yaml`:

```YAML
App\Entity\Series:
    properties:
        name:
            - NotBlank: ~
            - Length:
                min: 5
```

Remoção das constraints de validação da classe da entidade (elas foram transportadas para o arquivo `config\validator\validations.yaml`, apenas as anotações do Doctrine permaneceram):
```php
namespace App\Entity;

use App\Repository\SeriesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeriesRepository::class)]
class Series
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column]
        private ?string $name = ''
    )
    {
    }
    // ... getters e setters.
}
```
