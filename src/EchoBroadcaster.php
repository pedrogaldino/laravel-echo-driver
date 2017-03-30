<?php namespace PedroGaldino\EchoBroadcastDriver;

use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Broadcasting\BroadcastException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EchoBroadcaster extends Broadcaster
{
    /**
     * The Pusher SDK instance.
     *
     * @var \Pusher
     */
    protected $pusher;

    
    /**
     * Create a new broadcaster instance.
     *
     * @param  array  $conn_params
     */
    public function __construct($conn_params)
    {
        $this->pusher = new PusherProxy($conn_params['key'], null, $conn_params['app_id'], $conn_params['options'], $conn_params['options']['host'], $conn_params['options']['port']);
    }


    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function auth($request)
    {
        if (Str::startsWith($request->channel_name, ['private-', 'presence-']) &&
            ! $request->user()) {
            throw new HttpException(403);
        }

        $channelName = Str::startsWith($request->channel_name, 'private-')
                            ? Str::replaceFirst('private-', '', $request->channel_name)
                            : Str::replaceFirst('presence-', '', $request->channel_name);

        return parent::verifyUserCanAccessChannel(
            $request, $channelName
        );
    }


    /**
     * Return the valid authentication response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $result
     * @return mixed
     */
    public function validAuthenticationResponse($request, $result)
    {
        if (Str::startsWith($request->channel_name, 'private')) {
            return $this->decodePusherResponse(
                $this->pusher->socket_auth($request->channel_name, $request->socket_id)
            );
        } else {
            return $this->decodePusherResponse(
                $this->pusher->presence_auth(
                    $request->channel_name, $request->socket_id, $request->user()->getKey(), $result)
            );
        }
    }


    /**
     * Decode the given Pusher response.
     *
     * @param  mixed  $response
     * @return array
     */
    protected function decodePusherResponse($response)
    {
        return json_decode($response, true);
    }


    /**
     * Broadcast the given event.
     *
     * @param  array  $channels
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $socket = Arr::pull($payload, 'socket');

        $response = $this->pusher->trigger(
            $this->formatChannels($channels), $event, $payload, $socket, true
        );

        if ((is_array($response) && $response['status'] >= 200 && $response['status'] <= 299)
            || $response === true) {
            return;
        }

        throw new BroadcastException(
            is_bool($response) ? 'Failed to connect to Pusher.' : $response['body']
        );
    }

    /**
     * Get the Pusher SDK instance.
     *
     * @return \Pusher
     */
    public function getPusher()
    {
        return $this->pusher;
    }
}
