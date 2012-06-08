<?php

namespace Vespolina\CheckoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Vespolina\StoreBundle\Controller\AbstractController;
use Vespolina\CheckoutBundle\Process\ProcessStepInterface;

class ProcessController extends AbstractController
{
    public function processNavigatorAction(ProcessStepInterface $currentProcessStep)
    {
        $process = $currentProcessStep->getProcess();
        $processSteps = $process->getProcessSteps();

        return $this->render('VespolinaCheckoutBundle:Process:processNavigator.html.twig',
            array('currentProcessStep' => $currentProcessStep,
                  'processSteps' => $processSteps, ));
    }


}
