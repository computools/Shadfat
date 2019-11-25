public function handle()
{
$connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_LOGIN'), env('RABBITMQ_PASSWORD'));
$channel = $connection->channel();

$channel->queue_declare('save_book', false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) {
echo ' [x] Received ', $msg->body, "\n";
$this->itemSaver->processData($msg->body);
echo " [x] Done\n";
$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('save_entity, '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
$channel->wait();
}

$channel->close();
$connection->close();
echo " Exit\n";
}