<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\Validator\Constraints\ValidSSLLabsAnalyze;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SURFnet\SslLabs\Client;
use SURFnet\SslLabs\Dto\Host;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * Class FormController
 *
 * @Route("/subscription")
 */
final class SubscriptionController extends Controller
{
    /**
     * @Route("/{id}", name="subscription")
     * @Method("GET")
     * @ParamConverter("subscription", converter="synchronized_subscription")
     */
    public function getAction(Subscription $subscription)
    {
        if ($subscription->isForConnect()) {
            if ($subscription->isDraft()) {
                return $this->redirectToRoute('connect_draft_edit', array('id' => $subscription->getId()));
            }
            if ($subscription->isPublished()) {
                return $this->redirectToRoute('connect_published_edit', array('id' => $subscription->getId()));
            }
            if ($subscription->isFinished()) {
                return $this->redirectToRoute('connect_finished_thanks', array('id' => $subscription->getId()));
            }
        }

        if ($subscription->isForProduction()) {
            if ($subscription->isDraft()) {
                return $this->redirectToRoute('production_draft_edit', array('id' => $subscription->getId()));
            }
            if ($subscription->isFinished()) {
                return $this->redirectToRoute('production_finished_thanks', array('id' => $subscription->getId()));
            }
        }

        throw new NotFoundHttpException(
            sprintf(
                'Subscription with id "%s", environment "%s" and status "%s" cannot be redirected',
                $subscription->getId(),
                $subscription->getEnvironment(),
                $subscription->getStatus()
            )
        );
    }

    /**
     * @Method({"POST"})
     * @Route("/{id}/validate", name="validate")
     * @ParamConverter("subscription", converter="synchronized_subscription")
     */
    public function validateAction(Subscription $subscription, Request $request)
    {
        $formFactory = $this->get('subscription.form.factory');
        $form = $formFactory->buildForm($subscription, $request, false);

        $form->submit($request->request->get($form->getName()), false);

        return $this->get('validation_response_builder')->build($form);
    }

    /**
     * @Method("POST")
     * @Route("/{id}/validate-url", name="validate_url")
     */
    public function validateUrlAction(Request $request)
    {
        $subscriptionRequestData = $request->get('subscription');

        $validToken = $this->get('security.csrf.token_manager')->isTokenValid(
            new CsrfToken(
                'subscription',
                $subscriptionRequestData['token']
            )
        );
        if (!$validToken) {
            return new Response('Invalid CSRF token', 400);
        }

        $url = $request->get('url');

        if (empty($url)) {
            return new Response('No URL sent', 400);
        }

        $parsedUrl = parse_url($url);

        if (empty($parsedUrl['host'])) {
            return new Response('Unable to get host from url', 400);
        }

        # TODO: BaZo
        # TODO: disable SSLlabs
        $client = $this->get('ssllabs.client');

        $info = $client->info();
        if ($info->currentAssessments >= $info->maxAssessments) {
            return new Response('Maximum requests reached', 503);
        }

        /* TODO: (BaZo) disable SSLlabs for now */
        return new Response('OK', 200);

        $validator = $this->get('validator.ssllabs');
        $validator->validate(
            $url,
            new ValidSSLLabsAnalyze()
        );
        $hostDto = $validator->getLastHostDto();

        if (!$hostDto) {
            throw new \RuntimeException('No host from validator');
        }

        $endStatuses = array(Host::STATUS_ERROR, Host::STATUS_READY);
        if (!in_array($hostDto->status, $endStatuses)) {
            return new Response('Unable to validate host', 400);
        }

        $statusCode = 202; // Accepted

        if ($hostDto->status === Host::STATUS_ERROR) {
            $statusCode = 500; // Server Error
        }
        if ($hostDto->status === Host::STATUS_READY) {
            $statusCode = 200; // Okay
        }

        return new JsonResponse(
            array('violation' => $validator->getLastViolation()),
            $statusCode
        );
    }

    /**
     * @Method({"GET"})
     * @Route("/{id}/lock", name="lock")
     * @ParamConverter("subscription", converter="synchronized_subscription")
     */
    public function lockAction(Subscription $subscription)
    {
        if (!$this->get('lock.manager')->lock($subscription->getId())) {
            return new Response('', 423);
        }

        return new Response();
    }
}
