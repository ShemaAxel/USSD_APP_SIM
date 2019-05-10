<?php

/**
 * Transaction status constants. Contains the statuses in which a transaction
 * can be in during processing.
 *
 * @author Thomas Kioko <thomas.kioko@cellulant.com>
 */
class StatusCodes
{
    const KEY_ID = 41;
    /**
     * Overall Status Codes.
     */
    const SUCCESS_UPDATE = 2;
    /**
     * The customer cancelled validation and requested a reversal
     */
    const REVERSAL_REQUEST = 5;
    /**
     * There are no configured pay bills or the are inactive
     */
    const NO_ROUTING_INFO = 10;
    /**
     * Generic stat code.
     */
    const PAYMENT_NOT_IDENTIFIED = 11;
    /**
     * Generic overall status saved on channelRequests overall status column
     */
    const PAYMENT_PENDING_VALIDATION = 12;
    /**
     * Keywords have not been setup
     */
    const KEYWORD_MISSING = 13;
    /**
     * The alias exists but was not the one provided
     */
    const PAYMENT_NOT_MATCHED_AGAINST_ALIAS = 14;
    /**
     * You do not have an alias
     */
    const PAYMENT_MISSING_ALIAS = 15;

    /**
     * not matching
     */
    const GENERAL_EXCEPTION_OCCURRED = 104;
    /**
     *
     */
    const GENERIC_SUCCESS_STATUS_CODE = 173;
    /**
     *
     */
    const NUMBER_OF_SENDS = 0;
    
    /**
     * Resolution service for cellulant
     */
    const RESOLUTION_SERVICEID=  185;
    
    /**
     * active status
     */
    const ACTIVE =1;
}