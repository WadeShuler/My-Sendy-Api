<?php
namespace Sendy;

/**
 * A Constant Class Helper for Sendy API actions.
 *
 * These constants are to append the correct path to the base Sendy API URL.
 *
 * Example: `SendyActions::SUBSCRIBE`
 *
 * Will result in: `https://sendy.example.com/subscribe`
 *
 * @author Wade Shuler https://github.com/WadeShuler
 */
Class SendyActions
{
    /**
     * The subscribe action path to append to the sendyUrl
     * @var string
     */
    const SUBSCRIBE   = 'subscribe';

    /**
     * The unsubscribe action path to append to the sendyUrl
     * @var string
     */
    const UNSUBSCRIBE = 'unsubscribe';

    /**
     * The delete subscriber action path to append to the sendyUrl
     * @var string
     */
    const DELETE_SUBSCRIBER = '/api/subscribers/delete.php';

    /**
     * The subscription status action path to append to the sendyUrl
     * @var string
     */
    const SUBSCRIPTION_STATUS = '/api/subscribers/subscription-status.php';

    /**
     * The active subscriber count action path to append to the sendyUrl
     * @var string
     */
    const ACTIVE_SUBSCRIBER_COUNT = '/api/subscribers/active-subscriber-count.php';

    /**
     * The create or send action path to append to the sendyUrl
     * @var string
     */
    const CREATE_SEND_CAMPAIGN = '/api/campaigns/create.php';
}
