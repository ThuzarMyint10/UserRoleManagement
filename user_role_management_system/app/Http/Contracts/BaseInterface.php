<?php

namespace App\Http\Contracts;

interface BaseInterface
{
    public function all();
    public function find(int $id, array $includes = []);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
