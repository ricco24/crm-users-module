<?php

namespace Crm\UsersModule\User;

use Crm\ApplicationModule\User\UserDataProviderInterface;
use Crm\UsersModule\Repository\AddressesRepository;

class AddressesUserDataProvider implements UserDataProviderInterface
{
    private $addressesRepository;

    public function __construct(
        AddressesRepository $addressesRepository
    ) {
        $this->addressesRepository = $addressesRepository;
    }

    public static function identifier(): string
    {
        return 'addresses';
    }

    public function data($userId)
    {
        return [];
    }

    public function download($userId)
    {
        $addresses = $this->addressesRepository->getTable()->where(['user_id' => $userId])->fetchAll();

        if (!$addresses) {
            return [];
        }

        $returnAddresses = [];
        foreach ($addresses as $address) {
            $returnAddress = [
                'type' => $address->type,
                'created_at' => $address->created_at->format(\DateTime::RFC3339),
                'first_name' => $address->first_name,
                'last_name' => $address->last_name,
                'address' => $address->address,
                'number' => $address->number,
                'city' => $address->city,
                'zip' => $address->zip,
                'phone_number' => $address->phone_number,
            ];

            if ($address->country) {
                $returnAddress['country'] = $address->country->name;
            }
            if (!empty($address->ico)) {
                $returnAddress['ico'] = $address->ico;
            }
            if (!empty($address->dic)) {
                $returnAddress['dic'] = $address->dic;
            }
            if (!empty($address->icdph)) {
                $returnAddress['icdph'] = $address->icdph;
            }
            if (!empty($address->company_name)) {
                $returnAddress['company_name'] = $address->company_name;
            }

            $returnAddresses[] = $returnAddress;
        }

        return $returnAddresses;
    }

    public function downloadAttachments($userId)
    {
        return [];
    }

    public function protect($userId): array
    {
        return [];
    }

    /**
     * @param $userId
     * @throws \Exception
     */
    public function delete($userId, $protectedData = [])
    {
        $query = $this->addressesRepository->getTable()->where(['user_id' => $userId]);
        if (count($protectedData) > 0) {
            $query = $query->where('id NOT IN (?)', $protectedData);
        }

        $addresses = $query->fetchAll();
        $GDPRTemplateAddress = [
            'title' => 'GDPR removal',
            'first_name' => 'GDPR removal',
            'last_name' => 'GDPR removal',
            'address' => 'GDPR removal',
            'number' => 'GDPR removal',
            'city' => 'GDPR removal',
            'zip' => 'GDPR removal',
            'country_id' => null,
            'ico' => 'GDPR removal',
            'dic' => 'GDPR removal',
            'icdph' => 'GDPR removal',
            'company_name' => 'GDPR removal',
            'phone_number' => 'GDPR removal',
        ];

        foreach ($addresses as $address) {
            $this->addressesRepository->update($address, $GDPRTemplateAddress);
        }
    }

    public function canBeDeleted($userId): array
    {
        return [true, null];
    }
}