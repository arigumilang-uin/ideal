<?php

namespace App\Observers;

use App\Models\Jurusan;
use App\Models\User;
use App\Models\Role;

/**
 * Jurusan Observer
 * 
 * PURPOSE: 
 * - Auto-update Kaprodi name when assigned to Jurusan
 * - Auto-swap roles when kaprodi changes:
 *   - Old kaprodi → role becomes Guru
 *   - New kaprodi → role becomes Kaprodi
 */
class JurusanObserver
{
    /**
     * Handle the Jurusan "created" event.
     */
    public function created(Jurusan $jurusan): void
    {
        $this->syncNewKaprodi($jurusan, null);
    }

    /**
     * Handle the Jurusan "updated" event.
     */
    public function updated(Jurusan $jurusan): void
    {
        // If kaprodi_user_id changed
        if ($jurusan->wasChanged('kaprodi_user_id')) {
            $oldKaprodiId = $jurusan->getOriginal('kaprodi_user_id');
            $this->syncNewKaprodi($jurusan, $oldKaprodiId);
        } 
        // If only nama_jurusan changed, update current kaprodi name
        elseif ($jurusan->wasChanged('nama_jurusan')) {
            $this->updateKaprodiName($jurusan);
        }
    }
    
    /**
     * Sync new kaprodi and demote old kaprodi to Guru.
     * 
     * LOGIC:
     * 1. Old kaprodi (if exists and not Developer):
     *    - Check if still assigned to any other jurusan
     *    - If not → role becomes Guru, nama becomes "Guru"
     * 2. New kaprodi (if exists and not Developer):
     *    - Role becomes Kaprodi
     *    - Nama becomes "Kaprodi [Nama Jurusan]"
     *    - If was Wali Kelas → detach from kelas
     */
    private function syncNewKaprodi(Jurusan $jurusan, ?int $oldKaprodiId): void
    {
        $kaprodiRole = Role::where('nama_role', 'Kaprodi')->first();
        $guruRole = Role::where('nama_role', 'Guru')->first();
        
        if (!$kaprodiRole || !$guruRole) {
            \Log::warning('JurusanObserver: Kaprodi or Guru role not found in database.');
            return;
        }
        
        // STEP 1: Handle OLD kaprodi (demote to Guru)
        // Karena relasi one-to-one (1 kaprodi = 1 jurusan), langsung demote ke Guru
        if ($oldKaprodiId) {
            $oldKaprodi = User::find($oldKaprodiId);
            
            if ($oldKaprodi && $oldKaprodi->role?->nama_role !== 'Developer') {
                // Old kaprodi langsung jadi Guru (tidak perlu cek jurusan lain karena one-to-one)
                $oldKaprodi->updateQuietly([
                    'role_id' => $guruRole->id,
                    'nama' => 'Guru',
                ]);
                
                \Log::info("JurusanObserver: User {$oldKaprodi->username} demoted from Kaprodi to Guru.");
            }
        }
        
        // STEP 2: Handle NEW kaprodi (promote to Kaprodi)
        if ($jurusan->kaprodi_user_id) {
            $newKaprodi = User::find($jurusan->kaprodi_user_id);
            
            if ($newKaprodi && $newKaprodi->role?->nama_role !== 'Developer') {
                // CRITICAL: Jika user ini sudah kaprodi di jurusan LAIN, lepaskan dulu
                // Karena 1 kaprodi = 1 jurusan (one-to-one)
                Jurusan::where('kaprodi_user_id', $newKaprodi->id)
                    ->where('id', '!=', $jurusan->id)
                    ->update(['kaprodi_user_id' => null]);
                
                $updates = [
                    'nama' => "Kaprodi {$jurusan->nama_jurusan}",
                ];
                
                // If not already Kaprodi, update role
                if ($newKaprodi->role_id !== $kaprodiRole->id) {
                    $updates['role_id'] = $kaprodiRole->id;
                    
                    // If was Wali Kelas, detach from kelas
                    if ($newKaprodi->role?->nama_role === 'Wali Kelas') {
                        \App\Models\Kelas::where('wali_kelas_user_id', $newKaprodi->id)
                            ->update(['wali_kelas_user_id' => null]);
                        
                        \Log::info("JurusanObserver: User {$newKaprodi->username} was Wali Kelas, detached from kelas.");
                    }
                    
                    \Log::info("JurusanObserver: User {$newKaprodi->username} promoted to Kaprodi of {$jurusan->nama_jurusan}.");
                }
                
                $newKaprodi->updateQuietly($updates);
            }
        }
    }
    
    /**
     * Update kaprodi name when jurusan name changes.
     */
    private function updateKaprodiName(Jurusan $jurusan): void
    {
        if ($jurusan->kaprodi_user_id) {
            $kaprodi = User::find($jurusan->kaprodi_user_id);
            
            if ($kaprodi && $kaprodi->role?->nama_role !== 'Developer') {
                $newName = "Kaprodi {$jurusan->nama_jurusan}";
                if ($kaprodi->nama !== $newName) {
                    $kaprodi->updateQuietly(['nama' => $newName]);
                }
            }
        }
    }
}
