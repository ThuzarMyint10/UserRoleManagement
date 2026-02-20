<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected $service;
    protected $resourceClass;
    protected array $allowedIncludes = []; // whitelist relationships

    public function __construct($service, $resourceClass, array $allowedIncludes = [])
    {
        $this->service = $service;
        $this->resourceClass = $resourceClass;
        $this->allowedIncludes = $allowedIncludes;
    }

    /**
     * Extract valid includes from request based on whitelist
     */
    protected function getValidIncludes(Request $request): array
    {
        if (!$request->filled('include')) {
            return [];
        }

        $requestedIncludes = explode(',', $request->query('include'));
        return array_intersect($requestedIncludes, $this->allowedIncludes);
    }

    public function index(Request $request)
    {
        $validIncludes = $this->getValidIncludes($request);
        $collection = $this->service->all();

        if (!empty($validIncludes)) {
            $collection->load($validIncludes);
        }

        return $this->resourceClass::collection($collection);
    }

    public function show(Request $request, $id)
    {
        $validIncludes = $this->getValidIncludes($request);
        $model = $this->service->find($id, $validIncludes);

        return new $this->resourceClass($model);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $model = $this->service->create($data);
        return new $this->resourceClass($model);
    }

    public function update(Request $request, $id)
    {
        $model = $this->service->update($id, $request->all());
        return new $this->resourceClass($model);
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
