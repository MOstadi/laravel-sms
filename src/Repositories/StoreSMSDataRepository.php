<?php
/**
 * Created by PhpStorm.
 * User: amir
 * Date: 9/26/18
 * Time: 10:05 PM
 */

namespace Amiriun\SMS\Repositories;


use Amiriun\SMS\DataContracts\DeliverSMSDTO;
use Amiriun\SMS\DataContracts\ReceiveSMSDTO;
use Amiriun\SMS\DataContracts\SentSMSOutputDTO;
use Illuminate\Database\ConnectionInterface;

class StoreSMSDataRepository
{
    private $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param SentSMSOutputDTO $DTO
     *
     * @throws \Exception
     */
    public function storeSendSMSLog(SentSMSOutputDTO $DTO)
    {
        $store = $this->connection
            ->table('sms_logs')
            ->insert(
                [
                    'message_id'    => $DTO->messageId,
                    'message'       => $DTO->messageResult,
                    'sender_number' => $DTO->senderNumber,
                    'to'            => $DTO->to,
                    'is_delivered'  => false,
                    'connector'     => $DTO->connectorName,
                    'type'          => 'send',
                ]
            );
        if (!$store) {
            throw new \Exception("Error in store receive data.");
        }
    }

    /**
     * @param ReceiveSMSDTO $DTO
     *
     * @throws \Exception
     * @return void
     */
    public function storeReceiveSMSLog(ReceiveSMSDTO $DTO)
    {
        $store = $this->connection
            ->table('sms_logs')
            ->insert(
                [
                    'message_id'    => $DTO->messageId,
                    'message'       => $DTO->message,
                    'sender_number' => $DTO->senderNumber,
                    'to'            => $DTO->to,
                    'connector'     => $DTO->connectorName,
                    'sent_at'       => $DTO->sentAt,
                    'type'          => 'receive',
                ]
            );
        if (!$store) {
            throw new \Exception("Error in store receive data.");
        }
    }

    public function deliver(DeliverSMSDTO $DTO)
    {

        $getRecord = $this->connection->table(config('sms.logging.send_logs.table_name'))
            ->where('message_id', $DTO->messageId)
            ->where('connector', $DTO->connectorName);
        if (!$getRecord->exists()) {
            throw new \Exception("Record for delivering is not exist.");
        }
        $getRecord->update(['is_delivered', 1]);
    }

}