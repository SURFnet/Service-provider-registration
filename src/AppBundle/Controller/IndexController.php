<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use AppBundle\Form\ContactType;
use Doctrine\Common\Cache\ApcCache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IndexController
 */
class IndexController extends Controller
{
    /**
     * @Route("/", name="home")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $contact = $this->get('session')->get('form', new Contact());

        $form = $this->createForm(
            new ContactType(),
            $contact, array(
                'disabled' => !$this->getLock($contact->getId())
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            var_dump($form->getData());
            die('VALID');
        } elseif ($form->isSubmitted()) {
            var_dump($form->getData());
        }

        return $this->render(
            'index/index.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @Route("/save", name="save")
     *
     * @param Request $request
     *
     * @return Response
     * @todo: csrf is trouble, why?
     */
    public function saveAction(Request $request)
    {
        $contact = $this->get('session')->get('form', new Contact());

        $form = $this->createForm(
            new ContactType(),
            $contact, array(
                'disabled'        => !$this->getLock($contact->getId()),
                'csrf_protection' => false
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->get('session')->set('form', $contact);
        }

        return new Response();
    }

    /**
     * @Route("/validate", name="validate")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function validateAction(Request $request)
    {
        $contact = $this->get('session')->get('form', new Contact());

        $form = $this->createForm(new ContactType(), $contact, array('csrf_protection' => false));

        $form->submit($request->get($form->getName()), false);

        if (!$form->isValid()) {
            return new Response($form->getErrors(true, false), 400);
        }

        return new Response();
    }

    /**
     * @Route("/lock", name="lock")
     *
     * @return Response
     */
    public function lockAction()
    {
        if (!$this->getLock(10)) {
            return new Response('', 423);
        }

        return new Response();
    }

    /**
     * @param string $id
     *
     * @return bool
     * @todo: this is not atomic!
     */
    private function getLock($id)
    {
        /** @var ApcCache $cache */
        $cache = $this->get('cache');
        $session = $this->get('session');

        $cacheId = 'lock-' . $id;
        $sessionId = $session->getId();

        $lock = $cache->fetch($cacheId);

        // If there already is a lock for another session -> fail.
        if ($lock !== false && $lock !== $sessionId) {
            return false;
        }

        return $cache->save($cacheId, $sessionId, 12);
    }
}
