<?php

namespace AppBundle\Response;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ValidationReponseBuilder
{
    public function build(Form $form)
    {
        $response = array('data' => array(), 'errors' => array());

        /** @var FormInterface $field */
        foreach ($form as $field) {
            if ($field->count() > 1) {
                $response = $this->buildForNestedFields($field, $response);

            } else {
                if ($field->isSubmitted()) {
                    $response['data'][$field->getName()] = $field->getData();
                }

                if (!$field->isValid()) {
                    foreach ($field->getErrors(true) as $error) {
                        $response['errors'][$field->getName()][] = $error->getMessage();
                    }
                }
            }
        }

        return new JsonResponse($response, $form->isValid() ? 200 : 400);
    }

    /**
     * @param $parentField
     * @param $response
     * @return array
     */
    private function buildForNestedFields(FormInterface $parentField, array $response)
    {
        /** @var FormInterface $child */
        foreach ($parentField as $child) {
            if ($child->isSubmitted()) {
                $response['data'][$parentField->getName()][$child->getName()] = $child->getData();
            }

            if (!$child->isValid()) {
                foreach ($child->getErrors(true) as $error) {
                    $response['errors'][$parentField->getName()][$child->getName()][] = $error->getMessage();
                }
            }
        }
        return $response;
    }
}
