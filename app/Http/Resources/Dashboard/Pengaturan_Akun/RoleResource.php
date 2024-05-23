<?php

namespace App\Http\Resources\Dashboard\Pengaturan_Akun;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public $status;
    public $message;
    public $data;

    public function __construct($status, $message, $data)
    {
        parent::__construct($data);
        $this->status = $status;
        $this->message = $message;
    }

    public function toArray($request)
    {
        // Check if the resource is a paginator instance and adapt the response accordingly
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return [
                'status' =>  $this->status,
                'message' => $this->message,
                'data' => $this->formatData($this->resource->getCollection()),
                'links' => [
                    'first' => $this->resource->url(1),
                    'last' => $this->resource->url($this->resource->lastPage()),
                    'prev' => $this->resource->previousPageUrl(),
                    'next' => $this->resource->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $this->resource->currentPage(),
                    'from' => $this->resource->firstItem(),
                    'last_page' => $this->resource->lastPage(),
                    'per_page' => $this->resource->perPage(),
                    'to' => $this->resource->lastItem(),
                    'total' => $this->resource->total(),
                ],
            ];
        } else {
            return [
                'status' =>  $this->status,
                'message' => $this->message,
                'data' => $this->formatData(collect([$this->resource])),
            ];
        }
    }


    protected function formatData(Collection $collection)
    {
        return $collection->transform(function ($roles) {
            return [
                'id' => $roles->id,
                'name' => $roles->name,
                'deskripsi' => $roles->deskripsi,
                'initialValues' => $this->formatPermissions($roles->permissions),
                'created_at' => $roles->created_at,
                'updated_at' => $roles->updated_at
            ];
        });
    }

    protected function formatPermissions($permissions)
    {
        if ($permissions->isEmpty()) {
            return null;
        }

        $permissionTypes = ['view', 'create', 'edit', 'delete', 'import', 'export', 'reset'];

        $groupedPermissions = $permissions->groupBy('group')->map(function ($group, $groupName) use ($permissionTypes) {
            $permissionsArray = [];
            foreach ($permissionTypes as $type) {
                $hasPermission = $group->contains(function ($item) use ($type) {
                    return str_contains($item->name, $type);
                });
                $permissionsArray[$type] = $hasPermission ? true : null;
            }
            return [
                'name' => $groupName,
                'permissions' => $permissionsArray
            ];
        });

        return $groupedPermissions->values()->toArray();
    }
}
