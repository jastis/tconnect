<?php

namespace App\Domain\Repository;

use App\Factory\QueryFactory;
use DateTime;
use App\Database\TransactionInterface;

class CardRepository
{
    /**
     * @var QueryFactory The query factory
     */
    private $queryFactory;
    private $transaction;

    /**
     * The constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory,
                            TransactionInterface $transaction)
    {
        $this->queryFactory = $queryFactory;
        $this->transaction = $transaction;
    }


    public function requestCustomCard(array $data): array
    {
        $result = [];
        $this->transaction->begin();
        $this_data = [
            'back_image' => $data['back'],
            'front_image' => $data['front'],
            'exp_date' => Date('y:m:d', strtotime('+7 days')),
            'r_name' => $data['name'],
            'r_email' => $data['email'],
            'r_phone' => $data['phone'],
            'status' => 0,
            'amount' => $data['total'],
            'card_limit' => $data['cardsize'],
            'paycode' => $data['tx_ref'],
            'user_id' => $data['user_id']
        ];

        $trxvalue = [
            'type' => 2,
            'amount' => $data['total'],
            'user' => $data['user_id'],
            'template' => 0,
            'expiry_date' => Date('y:m:d', strtotime('+1 years')),
            'tx_ref' => $data['tx_ref']
        ];
        try {
            $newId = (int)$this->queryFactory->newInsert("card_request", $this_data)
                ->execute()
                ->lastInsertId();
            $result = ['id' => $newId, 'message' => 'Your request has been submitted Successfully', 'Code' => 200];

            $newtxn = (int)$this->queryFactory->newInsert("tbl_trxns", $trxvalue)
                ->execute()
                ->lastInsertId();



            // $subvalue = [
            //     'trxn_id'=>$newtxn,
            //     'user_id'=>$data['user_id'],
            //     'expiry_date'=>Date('y:m:d', strtotime('+1 years')),
            //     'template' => 0
            // ];
            // $query = $this->queryFactory->newSelect('subscription')->select(['*'])
            // ->andWhere(['template' => $data['template'], 'user_id' => $data['user']]);
            // $row = $query->execute()->fetch('assoc');
            // if ($row){

            //     $this->queryFactory->newUpdate('subscription')
            //     ->set($subvalue)
            //     ->andWhere(['user_id' => $data['user'],'template' => $data['template']])
            //     ->execute();
            // }else{
            //     $subid = (int)$this->queryFactory->newInsert("subscription", $subvalue)
            // ->execute()
            // ->lastInsertId();
            // }
            $this->transaction->commit();
        } catch (\Throwable $th) {
            $this->transaction->rollback();
            $result = ['id' => -1, 'message' => 'Your request failed!', 'Code' => 500];
        }
        return $result;
    }

    public function updateCustomSubscription(array $data): array
    {
        $result = [];
        $subvalue = [
            'trxn_id' => $data['trxn_id'],
            'user_id' => $data['user_id'],
            'expiry_date' => Date('y:m:d', strtotime('+1 years')),
            'template' => $data['template'],
            'status'=> 1,
            'max_user'=>$data['card_limit']
        ];
        $query = $this->queryFactory->newSelect('subscription')->select(['*'])
            ->andWhere(['template' => $data['template'], 'user_id' => $data['user_id']]);
        $row = $query->execute()->fetch('assoc');
        if ($row) {
            
            $this->queryFactory->newUpdate('subscription')
                ->set($subvalue)
                ->andWhere(['user_id' => $data['user_id'], 'template' => $data['template']])
                ->execute();
        } else {
            $subid = (int)$this->queryFactory->newInsert("subscription", $subvalue)
                ->execute()
                ->lastInsertId();
        }
        return $result;
    }


    public function createTheme(array $data): array
    {
        $this->transaction->begin();
        $result = [];
        $data['code'] = $data['type_id'] == 2 ? $data['code'] : null;
        $this_data = [
            'name' => $data['template_name'],
            'theme' => $data['theme'],
            'subscription' => $data['type_id'],
            'template_code' => $data['code'],
        ];

        try {
            $newId = (int)$this->queryFactory->newInsert("themes", $this_data)
                ->execute()
                ->lastInsertId();
            $result = ['id' => $newId, 'message' => 'New theme has been created', 'code' => 200];
            if (isset($data['req_id']) && $data['type_id'] == 2) {
                $query = $this->queryFactory->newSelect('card_request')->select(['user_id', 'r_email', 'paycode', 'r_phone','card_limit'])->where(['id' => $data['req_id']]);
                $row = $query->execute()->fetch('assoc');

               $staVal = [
                   'status' => 1
               ];

               $this->queryFactory->newUpdate('card_request')
               ->set($staVal)
               ->andWhere(['id' => $data['req_id']])
               ->execute();

                $query = $this->queryFactory->newSelect('tbl_trxns')->select(['id'])->where(['tx_ref' => $row['paycode']]);
                $rowtx = $query->execute()->fetch('assoc');

                $subvalue = [
                    'trxn_id' => $rowtx['id'],
                    'user_id' => $row['user_id'],
                    'expiry_date' => Date('y:m:d', strtotime('+1 years')),
                    'template' => $newId,
                    'card_limit'=>$row['card_limit']
                ];

                $this->updateCustomSubscription($subvalue);
            }
            $this->transaction->commit();
        } catch (\Throwable $th) {
            $this->transaction->rollback();
            $result = ['id' => -1, 'message' => 'Theme failed!', 'code' => 500];
        }
        return $result;
    }

    public function getTheme(int $id, string $user): array
    {
        // $query = $this->queryFactory->newSelect('usertbl')->select(['subscription'])->where(['user_id' => $user]);
        // $row = $query->execute()->fetch('assoc');
        //check if user is subscribed to the particular template
        $query1 = $this->queryFactory->newSelect('subscription')
        ->select(['template'])->where(['user_id' => $user, 'template' => $id, 'status' => 1]);
        $row1 = $query1->execute()->fetch('assoc');
        $sub_status = ($row1['template']) ? true : false;
        $result = [];
        $query = $this->queryFactory->newSelect('themes')->select(['*'])->where(['id' => $id]);
        $row = $query->execute()->fetch('assoc');
        $result = $row ? $row : $result;
        if ($result) {
            $result['subscribe'] = $sub_status;
        }
        return $result;
    }

    public function getThemeList(): array
    {
        $this->checkSubscription();
        $result = [];
        $query = $this->queryFactory->newSelect('themes')->select(['value' => 'id', 'label' => 'name', 'sub'=>'subscription'])
            ->where(['subscription <' => 2]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $row['label'] .= '-'.($row['sub']==0?'free':'subscribe');
            unset($row['sub']);
            $result[] = $row;
        }
        return $result;
    }


    public function getTemplateById(int $tid): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('themes')->select(['*'])
            ->where(['id' => $tid]);
        $row = $query->execute()->fetch('assoc');
        return $row?$row: $result;
    }

    public function getCustomCardList(): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('themes')->select(['themes.name', 
        'usertbl.first_name', 'usertbl.last_name', 'usertbl.email', 'usertbl.phone_no', 'subscription.status'])
        ->innerJoin('subscription', 'themes.id = subscription.template')
        ->innerJoin('usertbl', 'usertbl.user_id = subscription.user_id')
            ->where(['themes.subscription' => 2]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) { 
            $result[] = $row;
        }
        return $result;
    }
    public function setSubscription(array $data): array
    {
        $trxvalue = [
            'type' => 1,
            'amount' => $data['total'],
            'user' => $data['user'],
            'template' => $data['template'],
            'expiry_date' => Date('y:m:d', strtotime($data['subtype'] == 3 ? '+1 years' : ($data['subtype'] == 2 ? '+6 months' : '+3 months'))),
            'tx_ref' => $data['tx_ref'],

        ];
        try {
            $newtxn = (int)$this->queryFactory->newInsert("tbl_trxns", $trxvalue)
                ->execute()
                ->lastInsertId();

            $subvalue = [
                'trxn_id' => $newtxn,
                'user_id' => $data['user'],
                'expiry_date' => Date('y:m:d', strtotime($data['subtype'] == 3 ? '+1 years' : ($data['subtype'] == 2 ? '+6 months' : '+3 months'))),
                'template' => $data['template'],
                'status'=>1,
                'max_user'=>1
            ];

            $query = $this->queryFactory->newSelect('subscription')->select(['*'])
                ->andWhere(['template' => $data['template'], 'user_id' => $data['user']]);
            $row = $query->execute()->fetch('assoc');
            if ($row) {
                if ($row['expiry_date'] > (new DateTime('now'))->format('Y-m-d')) {
                    $addy =  $data['subtype'] == 3 ? '+1 years' : ($data['subtype'] == 2 ? '+6 months' : '+3 months');
                    $subvalue['expiry_date'] =  date('Y-m-d', strtotime($row['expiry_date'] . $addy));
                }

                $this->queryFactory->newUpdate('subscription')
                    ->set($subvalue)
                    ->andWhere(['user_id' => $data['user'], 'template' => $data['template']])
                    ->execute();
            } else {
                $subid = (int)$this->queryFactory->newInsert("subscription", $subvalue)
                    ->execute()
                    ->lastInsertId();
            }
            $values = ['subscription' => 1];
            $this->queryFactory->newUpdate('usertbl')
                ->set($values)
                ->andWhere(['user_id' => $data['user']])
                ->execute();
            $result = ['message' => 'Your subscription was successful', 'Code' => 200];
            return $result;
        } catch (\Throwable $th) {
            $result = ['message' => 'Sorry, we could not complete your subscription process. Please contact Admin', 'Code' => 500];
            return $result;
        }
    }


    public function getTemplateWithToken(string $token): array
    {
        $result = ['template_code' => 0];
        $query = $this->queryFactory->newSelect('themes')->select(['*'])->where(['template_code' => $token]);
        $row = $query->execute()->fetch('assoc');
        $result = $row ? $row : $result;
        $result['theme'] =  $result['theme'] ? json_decode($result['theme'], true, JSON_UNESCAPED_SLASHES) : [];
        return $result;
    }

    public function getAllCardRequest(): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('card_request')->select(['*'])->where(['status' => 0]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function getLatestCardRequest(): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('card_request')->select(['*'])->where(['status' => 0])
            ->limit(10)->page(1)->orderDesc('req_date');
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function countAllPendingCardRequest(): int
    {
        $result = 0;
        $query = $this->queryFactory->newSelect('card_request')->select(['id'])->where(['status' => 0]);
        $rows = $query->execute()->fetchAll('assoc');
        $result = count($rows);
        return $result;
    }

    public function countAllCards(): int
    {
        $this->checkSubscription();
        $result = 0;
        $query = $this->queryFactory->newSelect('cards')->select(['id']);
        $rows = $query->execute()->fetchAll('assoc');
        $result = count($rows);
        return $result;
    }

    public function getCardList(): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('profile')
            ->select([
                'profile.first_name', 'profile.last_name',
                'profile.organization', 'tname' => 'themes.name', 'tsub' => 'themes.subscription',
            ])
            ->innerJoin('themes', 'themes.id = profile.template');
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }
    public function getCardListByUser(string $user_id): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('profile')
            ->select([
                'profile.first_name', 'profile.last_name',
                'profile.organization', 'tname' => 'themes.name', 'tsub' => 'themes.subscription',
            ])
            ->innerJoin('themes', 'themes.id = profile.template')
            ->where(['profile.user_id'=>$user_id]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }
    public function getConnectionList(): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('cards');
        $query->select([
            'cards.id', 'cards.user_id',
            'cons' => $query->func()->count('cards.from_user'),
            'usertbl.first_name', 'usertbl.last_name'
        ])
            ->innerJoin('usertbl', 'usertbl.user_id = cards.user_id')
            
            ->group('cards.user_id');
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function getConnectionListByUser(string $user_id): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('cards');
        $query->select([
            'cards.id', 'cards.user_id',
            'cons' => $query->func()->count('cards.from_user'),
            'usertbl.first_name', 'usertbl.last_name'
        ])
            ->innerJoin('usertbl', 'usertbl.user_id = cards.from_user')
            ->where(['cards.user_id'=>$user_id])
            ->group('cards.from_user');
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function getConnectionDetails(string $user_id): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('cards');
        $query->select([
            'cards.id', 'cards.user_id',
            'usertbl.first_name', 'usertbl.last_name'
        ])
            ->innerJoin('usertbl', 'usertbl.user_id = cards.user_id');
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }
    public function countAllCustomCards(): int
    {
        $result = 0;
        $query = $this->queryFactory->newSelect('themes')->select(['id'])->where(['subscription' => 2]);
        $rows = $query->execute()->fetchAll('assoc');
        $result = count($rows);
        return $result;
    }

    public function countAllPaidCards(): int
    {
        $result = 0;
        $query = $this->queryFactory->newSelect('subscription')->select(['id']);
        $rows = $query->execute()->fetchAll('assoc');
        $result = count($rows);
        return $result;
    }

    public function getPaidUsers(): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('subscription')->select([
            'subscription.user_id',
            'subscription.expiry_date', 'subscription.status', 'usertbl.first_name', 'usertbl.last_name'
        ])
            ->innerJoin('usertbl', 'usertbl.user_id = subscription.user_id');
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {

            $result[] = $row;
        }
        return $result;
    }

    public function getAllUsers(): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('usertbl')->select(['first_name', 'last_name','email','phone_no']);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {

            $result[] = $row;
        }
        return $result;
    }

    public function countSubUsers(string $user_id): int
    {
        $result = 0;
        $query = $this->queryFactory->newSelect('subscription')->select(['subscription.template'])
        ->innerJoin('themes', 'themes.id = subscription.template')
            ->where(['subscription.user_id' => $user_id, 'subscription.status'=>1, 'themes.subscription'=>2 ]);
        $getSubTemp = $query->execute()->fetch('assoc');
        if ($getSubTemp){
        $query1 = $this->queryFactory->newSelect('profile')->select(['id'])
        ->where(['template' => $getSubTemp['template']]);
        $rows = $query1->execute()->fetchAll('assoc');
        $result = count($rows);
        }
        
        return $result;
    }

    public function getSubUsers(string $user_id): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('subscription')->select(['subscription.template'])
        ->innerJoin('themes', 'themes.id = subscription.template')
            ->where(['subscription.user_id' => $user_id, 'subscription.status'=>1, 'themes.subscription'=>2 ]);
        $getSubTemp = $query->execute()->fetch('assoc');
        if ($getSubTemp){
        $query1 = $this->queryFactory->newSelect('profile')->select(['id','first_name', 'last_name', 'email','cellphone'])
        ->where(['template' => $getSubTemp['template']]);
        $rows = $query1->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
    
        }
        
        return $result;
    }

public function deleteSubUsers (int $p_id){
    $this->queryFactory->newDelete('profile')
            ->andWhere(['id' => $p_id, ])
            ->execute();
}


    public function getExpiredSubscribers(): array
    {
        $this->checkSubscription();
        $result = [];
        $query = $this->queryFactory->newSelect('subscription')
            ->select([
                'subscription.user_id', 'themes.name', 'subscription.expiry_date',
                'subscription.status', 'usertbl.first_name', 'usertbl.last_name', 'usertbl.email', 'usertbl.phone_no'
            ])
            ->innerJoin('themes', 'themes.id = subscription.template')
            ->innerJoin('usertbl', 'usertbl.user_id = subscription.user_id')
            ->where(['subscription.expiry_date < ' => (new DateTime('now'))->format('Y-m-d H:i:s')]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function countActiveSubscribers(): int
    {
        $result = 0;
        $query = $this->queryFactory->newSelect('subscription')->select(['id'])
            ->where(['expiry_date >' => (new DateTime('now'))->format('Y-m-d H:i:s')]);
        $rows = $query->execute()->fetchAll('assoc');
        $result = count($rows);
        return $result;
    }

    public function getActiveSubscribers(): array
    {
        $this->checkSubscription();
        $result = [];
        $query = $this->queryFactory->newSelect('subscription')
            ->select([
                'subscription.user_id', 'themes.name', 'subscription.expiry_date',
                'subscription.status', 'usertbl.first_name', 'usertbl.last_name', 'usertbl.email', 'usertbl.phone_no'
            ])
            ->innerJoin('themes', 'themes.id = subscription.template')
            ->innerJoin('usertbl', 'usertbl.user_id = subscription.user_id')
            ->where(['subscription.expiry_date > ' => (new DateTime('now'))->format('Y-m-d H:i:s')]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }


    public function countAllCardsByUser(string $user_id): int
    {
        $result = 0;
        $query = $this->queryFactory->newSelect('profile')->select(['id'])
            ->where(['user_id' => $user_id]);
        $rows = $query->execute()->fetchAll('assoc');
        $result = count($rows);
        return $result;
    }

    public function countAllConnectionByUser(string $user_id): int
    {
        $result = 0;
        $query = $this->queryFactory->newSelect('cards')->select(['id'])
            ->where(['from_user' => $user_id]);
        $rows = $query->execute()->fetchAll('assoc');
        $result = count($rows);
        return $result;
    }

    public function countActiveSubscribersByUser(string $user_id): int
    {
        $result = 0;
        $query = $this->queryFactory->newSelect('subscription')->select(['id'])
            ->where(['expiry_date >' => (new DateTime('now'))->format('Y-m-d H:i:s'), 'user_id'=>$user_id]);
        $rows = $query->execute()->fetchAll('assoc');
        $result = count($rows);
        return $result;
    }

    public function countExpiredSubByUser(string $user_id): int
    {
        $result = [];
        $query = $this->queryFactory->newSelect('subscription')->select(['user_id', 'expiry_date', 'status'])
            ->where(['expiry_date < ' => (new DateTime('now'))->format('Y-m-d H:i:s'), 'user_id'=>$user_id]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {

            $result[] = $row;
        }
        return count($result);
    }
    public function countFreeUsers(): int
    {
        $result = 0;

        $query = $this->queryFactory->newSelect('profile')
            ->select('profile.id')
            ->innerJoin('themes', 'themes.id =profile.template')
            ->where(['themes.subscription' => 0]);
        $rows = $query->execute()->fetchall('assoc');
        $result = count($rows);
        return $result;
    }

    public function getFreeUsers(): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('profile')
            ->select(['profile.id', 'profile.first_name','profile.last_name',
            'profile.email','phone_no'=>'profile.cellphone','themes.name'])
            ->innerJoin('themes', 'themes.id =profile.template')
            ->where(['themes.subscription' => 0]);
        $rows = $query->execute()->fetchall('assoc');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }
    public function countExpiredSub(): int
    {
        $result = [];
        $query = $this->queryFactory->newSelect('subscription')->select(['user_id', 'expiry_date', 'status'])
            ->where(['expiry_date < ' => (new DateTime('now'))->format('Y-m-d H:i:s')]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {

            $result[] = $row;
        }
        return count($result);
    }

    public function getAllCardRequestById(int $id): array
    {
        $result = [];
        $query = $this->queryFactory->newSelect('card_request')->select(['*'])->where(['id' => $id]);
        $row = $query->execute()->fetch('assoc');
        $result = $row ? $row : $result;
        return $result;
    }

    public function getSubscriptionByUser(string $user_id): array
    {
        $this->checkSubscription();
        $result = [];
        $query = $this->queryFactory->newSelect('subscription')
        ->select(['subscription.*', 'themes.name','themes.id'])
        ->innerJoin('themes','themes.id=subscription.template')
        ->where(['subscription.user_id' => $user_id]);
        $rows = $query->execute()->fetchAll('assoc');
        foreach ($rows as $row) {

            $result[] = $row;
        }
        return $result;
    }

    private function checkSubscription():void{
        $values = [
            'status' => 0,
        ];
        $this->queryFactory->newUpdate('subscription')
            ->set($values)
            ->where(['subscription.expiry_date < ' => (new DateTime('now'))->format('Y-m-d H:i:s')])
            ->execute();
    }
}
