<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    // public function index(Request $request)
    // {
    //     $validIncludes = $this->getValidIncludes($request);
    //     $collection = $this->service->all();

    //     if (!empty($validIncludes)) {
    //         $collection->load($validIncludes);
    //     }

    //     return $this->resourceClass::collection($collection);
    // }

    public function index(Request $request)
    {
        $validIncludes = $this->getValidIncludes($request);

        // Auto-generate tag name from service model
        $modelClass = $this->service->model();
        $tag = Str::plural(Str::snake(class_basename($modelClass)));

        $baseKey = $tag . '_' . md5($request->fullUrl());
        $metaKey = $baseKey . '_meta';

        /*
        |--------------------------------------------------------------------------
        | Metadata Cache
        |--------------------------------------------------------------------------
        */
        $meta = Cache::tags([$tag])
            ->remember($metaKey, 3600, function () use ($modelClass) {
                $latestUpdate = $modelClass::max('updated_at');

                return [
                    'last_modified' => optional($latestUpdate)->timestamp ?? now()->timestamp,
                ];
            });

        $lastModified = gmdate('D, d M Y H:i:s', $meta['last_modified']) . ' GMT';
        $etag = '"' . md5($baseKey . $meta['last_modified']) . '"';

        /*
        |--------------------------------------------------------------------------
        | Conditional Request Check
        |--------------------------------------------------------------------------
        */
        if (
            $request->header('If-None-Match') === $etag ||
            strtotime($request->header('If-Modified-Since')) >= $meta['last_modified']
        ) {
            return new Response(null, 304, [
                    'ETag' => $etag,
                    'Last-Modified' => $lastModified,
                    'Cache-Control' => 'public, max-age=3600',
                ]);
           
        }

        /*
        |--------------------------------------------------------------------------
        | Data Cache
        |--------------------------------------------------------------------------
        */
        $collection = Cache::tags([$tag])
            ->remember($baseKey, 3600, function () use ($validIncludes) {

                Log::info("DB Hit!");
                $data = $this->service->all();

                if (!empty($validIncludes)) {
                    $data->load($validIncludes);
                }

                return $data;
            });

        return $this->resourceClass::collection($collection)
            ->response()
            ->header('Cache-Control', 'public, max-age=3600, must-revalidate')
            ->header('ETag', $etag)
            ->header('Last-Modified', $lastModified);
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
