Porter Quick Start Guide for Symfony
====================================

This quick start guide will walk through integrating Porter into a new Symfony project from scratch and assumes we already have a PHP 8.1 environment set up with Composer. This guide is based on a real-world use-case, used in production on [Steam 250][]. If we want to integrate Porter into an existing Symfony project, simply skip the Composer steps. If you encounter any other errors or get stuck, don't hesitate to file an issue.

Let's start by creating a new Symfony 5 project in an empty directory using the following command. Ensure the current working directory is set to the empty project directory.

```sh
composer create-project symfony/skeleton . ^5
```

>Note: The Steam provider (used below) requires [Amp v3][], which is currently in beta, so we need to allow beta dependencies temporarily. This can be enabled with the following command.
> ```sh
> composer config minimum-stability beta
> composer config prefer-stable true
> ```

Let's start with the [Steam provider][] for Porter by including it in our `composer.json` with the following command.

```sh
composer require --with-dependencies provider/steam
```

>Note: We specify the *with dependencies* flag because some shared dependencies between the provider and our current Symfony project may have mismatched versions, so they must be allowed to up/downgrade as necessary to work together.

Now the provider is installed along with all its dependencies, including Amp and Porter herself.

In this simple exercise, we will use the Steam provider to display a list of all the app IDs for every app available on [Steam][]. We're going to start coding now, so let's fire up our favourite editor. Start by creating a new `AppListAction` in our existing `src/Controller` directory. We're following the [ADR][] pattern rather than MVC, so we could rename the *Controller* directory to *Action*, too, but we will refrain for now, to keep things simple. Actions only handle a single route, using the `__invoke` method, so let's add that, too.

```php
<?php
declare(strict_types=1);

namespace App\Action;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AppListAction extends AbstractController
{
    public function __invoke()
    {
    }
}
```

Let's make our new action act as the home page for our application by making it respond to the `/` route path.

```diff
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
+use Symfony\Component\Routing\Annotation\Route;

+    #[Route('/')]
     public function __invoke()
```

We're using annotations because they're easiest to implement, but in order for this to work, we need to ensure the Doctrine annotations library is installed.

```sh
composer require doctrine/annotations
```

Let's just fill in the rest of the method with a stub, so we can test our application is working so far. The complete list of Steam app IDs is very long (over 150,000) so we will want to use a `StreamedResponse`.

```diff
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
+use Symfony\Component\HttpFoundation\Response;
+use Symfony\Component\HttpFoundation\StreamedResponse;
 use Symfony\Component\Routing\Annotation\Route;

-    public function __invoke()
+    public function __invoke(): Response
     {
+        return new StreamedResponse(
+            fn () => print 'Hello, Porter!',
+        );
     }
```

Start a web server to [view the home page](http://localhost).

```sh
php -S localhost:80 public/index.php
```

We should see *Hello, Porter!* displayed in our browser. If you see the default Symfony *welcome* page, ensure `doctrine/annotations` was installed correctly.

To gain access to the Steam data we need to inject Porter into our action.

```diff
+use ScriptFUSION\Porter\Porter;
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

-    public function __invoke(): Response
+    public function __invoke(Porter $porter): Response
```

Refreshing our browser now will display an error because we haven't told Symfony how to configure Porter as a service yet:

>`RuntimeException` Cannot autowire argument $porter of "App\Controller\AppListAction()": it references class "ScriptFUSION\Porter\Porter" but no such service exists.

Let's append the Porter service to `config/services.yaml`. Porter requires a container of providers, so let's inject the *providers* service into Porter.

```yaml
    ScriptFUSION\Porter\Porter:
      arguments:
        - '@providers'
```

Of course, this *providers* service does not exist yet, but we can create it by [defining a service locator][]. We only have one provider at this time, the `SteamProvider`, so let's ensure it's added to the locator so Porter can use it.

```yaml
    providers:
        class: Symfony\Component\DependencyInjection\ServiceLocator
        arguments:
            -
                - '@ScriptFUSION\Porter\Provider\Steam\SteamProvider'
```

>Note: We do not use the `!service_locator` shortcut to implicitly create the service locator due to a [Symfony bug](https://github.com/symfony/symfony/issues/48454) that misnames services added in this way.

Finally, since `SteamProvider` is third-party code, Symfony requires us to explicitly register it as a service, but we don't need to customize it in any way, so we can just specify its configuration as the tilde (`~`) to use the defaults.

```yaml
    ScriptFUSION\Porter\Provider\Steam\SteamProvider: ~
```

Refreshing our browser now should recompile the Symfony DI container and show us the same message as before (without errors). Porter is now injected into our action, ready to use!

```diff
 use ScriptFUSION\Porter\Porter;
+use ScriptFUSION\Porter\Provider\Steam\Resource\GetAppList;
+use ScriptFUSION\Porter\Specification\ImportSpecification;
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

-            fn () => print 'Hello, Porter!',
+            function () use ($porter): void {
+                foreach ($porter->import(new ImportSpecification(new GetAppList())) as $app) {
+                    echo "$app[appid]\n";
+                }
+            },
```

We iterate over each result from importing `GetAppList` and emit it using `echo`, appending a new line. Viewing the results in our browser shows us lots of numbers (the ID of each Steam app) but it does not respect the new line character (`\n`) because it renders as HTML by default. Let's fix that by specifying the correct mime type. Below is the completed code, including the new `content-type` header:

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Provider\Steam\Resource\GetAppList;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

final class AppListAction extends AbstractController
{
    #[Route('/')]
    public function __invoke(Porter $porter): Response
    {
        return new StreamedResponse(
            function () use ($porter): void {
                foreach ($porter->import(new ImportSpecification(new GetAppList())) as $app) {
                    echo "$app[appid]\n";
                }
            },
            headers: ['content-type' => 'text/plain'],
        );
    }
}
```

This should output a long list of numbers, one on each line, in the browser. For example:

>2170321  
1825161  
1897482  
2112761  
1829051  
...

We now have a Porter service defined that can be injected into as many services or actions as we wish. We can add as many [providers] to the `providers` service locator as we want, without any performance impact, since each service is lazy-loaded when required.

This just scratches the surface of Porter without going into any details. Explore the [rest of the manual][Readme] to gain a fuller understanding of the features at your disposal.

⮪ [Back to main Readme][Readme]


  [Readme]: https://github.com/ScriptFUSION/Porter/blob/master/README.md#quick-start
  [Steam provider]: https://github.com/Provider/Steam
  [Steam 250]: https://steam250.com
  [Steam]: https://store.steampowered.com
  [ADR]: https://github.com/pmjones/adr
  [Amp v3]: https://v3.amphp.org
  [Defining a Service Locator]: https://symfony.com/doc/current/service_container/service_subscribers_locators.html#defining-a-service-locator
  [Providers]: https://github.com/provider
