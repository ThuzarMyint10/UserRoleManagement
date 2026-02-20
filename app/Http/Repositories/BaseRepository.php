<?php

namespace App\Http\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Http\Contracts\BaseInterface;

class BaseRepository implements BaseInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->paginate();
    }

    public function find(int $id, array $includes = [])
    {
        return $this->model->with($includes)->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $record = $this->find($id);
        $record->update($data);
        return $record;
    }

    public function delete(int $id)
    {
        $record = $this->find($id);
        return $record->delete();
    }
}
