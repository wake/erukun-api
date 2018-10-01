<?php

  use Symfony\Component\Form\FormRenderer;


  /**
   *
   * Twig bootstrap
   *
   */
  $app->register (new Silex\Provider\TwigServiceProvider (), [

    // Base path
    'twig.path' =>  _RESOURCE . '/view',

    // Cache & autoload
    'twig.options' => [
      'cache' => _STORAGE . '/twig/caches',
      'auto_reload' => true
    ],
  ]);


  //
  // Fix Twig_Error_Runtime - Unable to load the "Symfony\Component\Form\FormRenderer" runtime.
  //
  // @Ref: https://github.com/silexphp/Silex/pull/1571#issuecomment-349463126
  //
  $app->extend ('twig.runtimes', function ($array) {

    $array[FormRenderer::class] = 'twig.form.renderer';

    return $array;
  });
