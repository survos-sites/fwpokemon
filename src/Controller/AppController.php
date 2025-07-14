<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Bundle\MenuBundle\KnpMenuEvents;
//fw-bundle
use Survos\FwBundle\Event\KnpMenuEvent;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Knp\Menu\FactoryInterface;
use Survos\FwBundle\Service\FwService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AppController extends AbstractController
{
    //construct
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        protected FactoryInterface $factory,
        private FwService $fwService,
    ) {
    }

    #[Route('/app', name: 'app_app')]
    public function index(): Response
    {
        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }

    #[Route('/{_locale}/{configCode}', name: 'project_start', options: ['expose' => true], methods: ['GET'])]
    public function project(Request $request, string $configCode): Response
    {
        //we ll hardcode the config to pokemon for now
        $configCode = 'pokemon';

        $templates = [];
        // iterate through the page and tab routes to create templates, which will be rendered in the main page.
        $menu = $this->factory->createItem($options['name'] ?? KnpMenuEvent::class);
        foreach ([
            KnpMenuEvent::MOBILE_TAB_MENU  => 'tab',
                     KnpMenuEvent::MOBILE_UNLINKED_MENU => 'page',
                 ] as $eventName=>$type) {
            $options = [];
            $options = (new OptionsResolver())
                ->setDefaults([

                ])
                ->resolve($options);
            $this->eventDispatcher->dispatch(new KnpMenuEvent($menu, $this->factory, options: $options, configCode: $configCode), $eventName);
            foreach ($menu->getChildren() as $route=>$child) {
                    $template = "pages/$route.html.twig";
                    $params = [
                        'type' => $type,
                        'route' => $route,
                        'template' => $template,
                        'debug' => $request->get('debug', false),
                    ];
                    $templates[$route] = $this->renderView($template, $params);
                try {
                } catch (\Exception $e) {
                    dd($route, $template, $e->getMessage(), $e);
                }
            }
        }

        $configs = $this->fwService->getConfigs();

        $config = $configs[strtolower($configCode)];

        //dd($config);

        return $this->render('@SurvosFw/start.html.twig', [
            'templates' => $templates,
            'appConfig' => $this->fwService->getConfig(),
            'config' => $this->fwService->getConfigs()[strtolower($configCode)],
            'locale' => $request->getLocale(),
            'configCode' => $configCode,
            'tabs' => ['tabs']??['info'],
            'playNow' => $request->get('playNow', true),
            ''
        ]);
    }

    #[Route('/{_locale}/pages/{page}', name: 'app_page', priority: 400)]
    public function page(string $page): Response
    {
        return $this->render("pages/$page.html.twig", [

        ]);
    }
}
