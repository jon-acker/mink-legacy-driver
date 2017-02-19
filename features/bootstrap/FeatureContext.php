<?php

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawMinkContext
{
    /**
     * @Given AAA
     */
    public function aaa()
    {
        $this->getSession()->visit('/form');

        $this->getSession()->getPage()->fillField('name', 'Carlos');
        $this->getSession()->getPage()->fillField('surname', 'Ortega');
        $this->getSession()->getPage()->pressButton('send');

        echo $this->getSession()->getPage()->getContent();
    }

    /**
     * @When BBB
     */
    public function bbb()
    {
    }

    /**
     * @Then CCC
     */
    public function ccc()
    {
    }
}
