<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* home.twig */
class __TwigTemplate_3f3054c63b628445497b63540397d5c7 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 1
        return "components/layout.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->loadTemplate("components/layout.twig", "home.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 4
        yield "\t";
        // line 5
        yield "\t<div class=\"w-full h-full flex flex-col justify-center items-center\">
\t\t<div class=\"space-y-6 flex flex-col justify-center items-center\">
\t\t\t<h1 class=\"text-green-500 text-8xl\">PHPCore</h1>
\t\t\t<p class=\"text-3xl\">A lightweight PHP Framework to create a full-stack web applications.</p>
\t\t</div>
\t\t<div class=\"flex justify-center gap-x-16 mt-6\">
\t\t\t<div>
\t\t\t\t<h3 class=\"text-green-500 text-4xl mb-5\">Backend</h3>
\t\t\t\t<ul class=\"list-disc text-2xl\">
\t\t\t\t\t<li>
\t\t\t\t\t\t<a href=\"https://www.php.net\" target=\"_blank\">PHP</a>
\t\t\t\t\t</li>
\t\t\t\t\t<li>
\t\t\t\t\t\t<a href=\"https://www.slimframework.com\" target=\"_blank\">SlimPHP</a>
\t\t\t\t\t</li>
\t\t\t\t\t<li>
\t\t\t\t\t\t<a href=\"https://www.php-di.org\" target=\"_blank\">PHP-DI Container</a>
\t\t\t\t\t</li>
\t\t\t\t\t<li>
\t\t\t\t\t\t<a href=\"https://www.mysql.com/\">MySQL</a>
\t\t\t\t\t</li>
\t\t\t\t\t<li>
\t\t\t\t\t\t<a href=\"https://www.doctrine-project.org\" target=\"_blank\">Doctrine ORM and Migrations</a>
\t\t\t\t\t</li>
\t\t\t\t\t<li>
\t\t\t\t\t\t<a href=\"https://symfony.com/doc/current/index.html\" target=\"_blank\">Symfony Components like Console, Mailer and so on.</a>
\t\t\t\t\t</li>
\t\t\t\t</ul>
\t\t\t</div>
\t\t\t<div>
\t\t\t\t<h3 class=\"text-green-500 text-4xl mb-5\">Frontend</h3>
\t\t\t\t<ul class=\"list-disc text-2xl\">
\t\t\t\t\t<li>
\t\t\t\t\t\t<a href=\"https://www.slimframework.com/docs/v4/features/twig-view.html\" target=\"_blank\">Twig-View Templates</a>
\t\t\t\t\t</li>
\t\t\t\t\t<li>
\t\t\t\t\t\t<a href=\"https://www.tailwindcss.com\" target=\"_blank\">TailwindCSS</a>
\t\t\t\t\t</li>
\t\t\t\t</ul>
\t\t\t</div>
\t\t</div>
\t</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "home.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  60 => 5,  58 => 4,  51 => 3,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "home.twig", "C:\\xampp\\htdocs\\framework\\resources\\views\\home.twig");
    }
}
