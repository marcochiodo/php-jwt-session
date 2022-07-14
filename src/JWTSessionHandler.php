<?php
namespace mrblue\JWTSession;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTSessionHandler implements \SessionHandlerInterface , \SessionIdInterface , \SessionUpdateTimestampHandlerInterface {

    private string $path;
    private array $payload = [];
    private \DateTime $last_update;

    private string $private_key;
    private string $public_key;
    private string $algorithm;


    function __construct( string $private_key , string $public_key , string $algorithm ) {
        $this->private_key = $private_key;
        $this->public_key = $public_key;
        $this->algorithm = $algorithm;
    }

    function open(string $path, string $name) : bool {
        $this->path = $path;
        return true;
    }

    function read(string $id): string|false {

        try {
            $decoded = JWT::decode($id, new Key($this->public_key, $this->algorithm));
        } catch (\Throwable $th) {
            return '';
        }
        
        if( ! $decoded ){
            return '';
        }

        $iss = $decoded->iss ?? '';

        if( $iss !== $this->path ){
            return '';
        }

        $this->payload = (array) $decoded;
        return $this->payload['data'] ?? '';
    }

    function write(string $id, string $data): bool {
        $this->payload['data'] = $data;
        return true;
    }

    function gc(int $max_lifetime): int|false {
        return 0;
    }

    function destroy(string $id): bool {
        $this->payload['data'] = [];
        return true;
    }

    function close(): bool {
        return true;
    }

    function create_sid(): string {

        if( ! $this->last_update && empty($this->payload['iat']) ){
            $this->last_update = new \DateTime();
        }

        if( $this->last_update ){
            $this->payload['iat'] = $this->last_update->getTimestamp();
        }

        $ttl = (int) ini_get('session.gc_maxlifetime');
        $this->payload['exp'] = time() + $ttl;

        return JWT::encode($this->payload, $this->private_key, $this->algorithm);
    }

    function updateTimestamp(string $id, string $data): bool {
        $this->last_update = new \DateTime();
        return true;
    }

    function validateId(string $id): bool {
        return true;
    }

}

