<?php

namespace OffbeatWP\Mail\Hooks;

use OffbeatWP\Hooks\AbstractAction;
use PHPMailer;

class DeliveryMailsOnlyToWhitelistAction extends AbstractAction
{
    public function action(PHPMailer $phpMailer): void
    {
        // Get Delivery Whitelist
        $deliveryWhitelist = config('mail.delivery_whitelist');

        if (!is_iterable($deliveryWhitelist)) {
            return;
        }

        // Get and filter TO addresses from whitelist
        $toAddresses = $phpMailer->getToAddresses();
        $toAddresses = $this->filterAddressesByWhitelist($toAddresses, $deliveryWhitelist->all());

        // if no TO addresses anymore, send mail to void
        if (!$toAddresses) {
            $phpMailer->Mailer = 'void';
            return;
        }

        $phpMailer->clearAddresses();
        foreach ($toAddresses as $toAddress) {
            $phpMailer->addAddress($toAddress[0], $toAddress[1] ?? null);
        }

        // Get and filter CC addresses from whitelist
        $ccAddresses = $this->filterAddressesByWhitelist($phpMailer->getCcAddresses(), $deliveryWhitelist->all());
        $phpMailer->clearCCs();
        foreach ($ccAddresses as $ccAddress) {
            $phpMailer->addCC($ccAddress[0], $ccAddress[1] ?? null);
        }

        // Get and filter BCC addresses from whitelist
        $bccAddresses = $this->filterAddressesByWhitelist($phpMailer->getBccAddresses(), $deliveryWhitelist->all());
        $phpMailer->clearBCCs();
        foreach ($bccAddresses as $bccAddress) {
            $phpMailer->addBCC($bccAddress[0], $bccAddress[1] ?? null);
        }
    }

    /**
     * @param array $addresses
     * @param string[] $whitelist
     * @return string[]
     */
    public function filterAddressesByWhitelist(array $addresses, array $whitelist): array
    {
        $filteredAddresses = [];

        foreach ($addresses as $address) {
            if ($this->checkAddressByWhitelist($address[0], $whitelist)) {
                $filteredAddresses[] = $address;
            } else {
                $erroredMail = json_encode($address);
                error_log("Recipient filtered because of delivery_whitelist settings: ".$erroredMail);
            }
        }

        return $filteredAddresses;
    }

    public function checkAddressByWhitelist(string $address, array $whitelist): bool
    {
        foreach ($whitelist as $whitelistPattern) {
            if (preg_match('/' . $whitelistPattern . '/', $address)) {
                return true;
            }
        }

        return false;
    }
}
