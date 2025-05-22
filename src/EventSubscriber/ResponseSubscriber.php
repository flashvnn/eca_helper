<?php

namespace Drupal\eca_helper\EventSubscriber;

use Drupal\eca_helper\Plugin\Action\HeaderRemove;
use Drupal\eca_helper\Plugin\Action\HeaderSet;
use Drupal\eca_helper\Service\CookieHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Handler response event.
 */
class ResponseSubscriber implements EventSubscriberInterface {

  /**
   * Kernel response event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   Response event.
   */
  public function onKernelResponse(ResponseEvent $event): void {
    $cookies = CookieHelper::getCookies();
    if (!empty($cookies)) {
      foreach ($cookies as $name => $data) {
        $cookie = new Cookie($name, $data['value'], $data['expire'], $data['path'], NULL, TRUE, TRUE);
        $event->getResponse()->headers->setCookie($cookie);
      }
    }
    if (!empty(HeaderSet::$headers)) {
      foreach (HeaderSet::$headers as $k => $v) {
        $event->getResponse()->headers->set($k, $v);
      }
    }
    if (!empty(HeaderRemove::$headers)) {
      foreach (HeaderRemove::$headers as $v) {
        is_string($v) && $event->getResponse()->headers->remove($v);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => ['onKernelResponse'],
    ];
  }

}
