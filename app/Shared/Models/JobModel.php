<?php

namespace App\Shared\Models;

use CodeIgniter\Model;

class JobModel extends Model
{
    protected $table            = 'jobs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['queue', 'payload', 'attempts', 'available_at', 'reserved_at', 'created_at'];

    public function push($queue, $payload, $delay = 0)
    {
        $availableAt = date('Y-m-d H:i:s', time() + $delay);
        return $this->insert([
            'queue'        => $queue,
            'payload'      => is_array($payload) ? json_encode($payload) : $payload,
            'available_at' => $availableAt,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function getNextJob($queue = 'default')
    {
        $this->db->transStart();

        $job = $this->where('queue', $queue)
                    ->where('available_at <=', date('Y-m-d H:i:s'))
                    ->where('reserved_at', null)
                    ->orderBy('available_at', 'ASC')
                    ->first();

        if ($job) {
            $this->update($job['id'], [
                'reserved_at' => date('Y-m-d H:i:s'),
                'attempts'    => $job['attempts'] + 1
            ]);
        }

        $this->db->transComplete();

        return $job;
    }
}
