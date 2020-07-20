<?php

/**
 * This file contains the "GraphQLAuthFailure" class.
 *
 * @category SilverStripe_Project
 * @package SDLT
 * @author  Catalyst I.T. SilverStripe Team 2018 <silverstripedev@catalyst.net.nz>
 * @copyright NZ Transport Agency
 * @license BSD-3
 * @link https://www.catalyst.net.nz
 */

namespace NZTA\SDLT\GraphQL;

use Exception;

/**
 * Class GraphQLAuthFailure
 *
 * This exception will be thrown when user is not authenticated in GraphQL API call
 */
class GraphQLAuthFailure extends Exception
{
    public function __construct()
    {
        parent::__construct('You are not authorized', 403);
    }
}
