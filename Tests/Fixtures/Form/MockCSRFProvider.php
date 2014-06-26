<?php
/**
 * User: Jonathan Chan <jchan@malwarebytes.org>
 * Date: 6/26/14
 * Time: 1:26 AM
 */


namespace Nelmio\ApiDocBundle\Tests\Fixtures\Form;


class MockCSRFProvider {
    public function generateCsrfToken($intention=null) {
        return "Test Token";
    }
} 