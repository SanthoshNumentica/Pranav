<?php

namespace App\Services;

use App\Models\Referer;
use Illuminate\Database\Eloquent\Builder;

class RefererService
{
    /**
     * Create or update a referer based on details array (orchestration helper).
     */
    public function createOrUpdateReferer(array $data): int
    {
        if (!empty($data['referer_fk_id'])) {
            $referer = Referer::find($data['referer_fk_id']);
            if ($referer) {
                $updateData = array_filter([
                    'title_id' => $data['title_fk_id'] ?? null,
                    'name' => $data['name'] ?? null,
                    'mobile_no' => $data['mobile_no'] ?? null,
                    'referer_type_id' => $data['referer_type_id'] ?? null,
                    'hospital_name' => $data['hospital_name'] ?? null,
                ], fn($v) => !is_null($v));
                
                if (!empty($updateData)) {
                    $updateData['modified_by'] = auth()->id();
                    $referer->update($updateData);
                }
                return $referer->id;
            }
        }
        
        $referer = $this->createReferer([
            'title_id' => $data['title_fk_id'] ?? null,
            'name' => $data['name'],
            'mobile_no' => $data['mobile_no'] ?? null,
            'referer_type_id' => $data['referer_type_id'] ?? null,
            'hospital_name' => $data['hospital_name'] ?? null,
            'added_by' => auth()->id()
        ]);
        
        return $referer->id;
    }

    /**
     * Create a new referer.
     */
    public function createReferer(array $data): Referer
    {
        $data['referer_id'] = $this->generateRefererId();
        $data['status'] = 'active';
        $data['added_by'] = $data['added_by'] ?? auth()->id();
        
        return Referer::create($data);
    }

    /**
     * Update an existing referer.
     */
    public function updateReferer(int $id, array $data): Referer
    {
        $referer = Referer::findOrFail($id);
        $data['modified_by'] = auth()->id();
        $referer->update($data);
        return $referer;
    }

    /**
     * Generate a sequential referer_id in the format REF0001.
     */
    public function generateRefererId(): string
    {
        $last = Referer::withTrashed()->whereNotNull('referer_id')->orderBy('id', 'desc')->first();
        $nextId = $last ? ((int) substr($last->referer_id, 3)) + 1 : 1;
        return 'REF' . str_pad((string) $nextId, 3, '0', STR_PAD_LEFT);
    }
}
