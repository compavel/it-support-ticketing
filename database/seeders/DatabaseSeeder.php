<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin IT',
            'email' => 'admin@sekolah.sch.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Technician
        $technician1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@sekolah.sch.id',
            'password' => Hash::make('password'),
            'role' => 'technician',
            'is_active' => true,
        ]);

        $technician2 = User::create([
            'name' => 'Andi Pratama',
            'email' => 'andi@sekolah.sch.id',
            'password' => Hash::make('password'),
            'role' => 'technician',
            'is_active' => true,
        ]);

        // Users
        $user1 = User::create([
            'name' => 'Siti Rahayu',
            'email' => 'siti@sekolah.sch.id',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $user2 = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi@sekolah.sch.id',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
        ]);

        // Sample Tickets
        $tickets = [
            [
                'user_id' => $user1->id,
                'technician_id' => $technician1->id,
                'title' => 'Laptop tidak bisa menyala',
                'description' => 'Laptop di lab komputer 1 tidak bisa menyala sama sekali. Sudah dicoba colok listrik tetap tidak ada respon. Lampu indikator mati.',
                'category' => 'hardware',
                'priority' => 'high',
                'status' => 'in_progress',
            ],
            [
                'user_id' => $user2->id,
                'technician_id' => $technician2->id,
                'title' => 'WiFi tidak bisa konek di gedung B',
                'description' => 'Beberapa guru melaporkan tidak bisa konek WiFi di gedung B lantai 2. Sinyal terlihat ada tapi tidak bisa akses internet.',
                'category' => 'jaringan',
                'priority' => 'urgent',
                'status' => 'open',
            ],
            [
                'user_id' => $user1->id,
                'technician_id' => null,
                'title' => 'Microsoft Word error saat buka file',
                'description' => 'Ketika membuka file .docx tertentu, Microsoft Word crash dan menampilkan error "Word experienced an error trying to open the file".',
                'category' => 'software',
                'priority' => 'medium',
                'status' => 'open',
            ],
            [
                'user_id' => $user2->id,
                'technician_id' => $technician1->id,
                'title' => 'Proyektor tidak tampil di ruang rapat',
                'description' => 'Proyektor di ruang rapat utama menyala tapi tidak ada tampilan dari laptop. Kabel HDMI sudah dicoba ganti tetap tidak bisa.',
                'category' => 'hardware',
                'priority' => 'high',
                'status' => 'resolved',
                'resolved_at' => now()->subDay(),
            ],
            [
                'user_id' => $user1->id,
                'technician_id' => $technician2->id,
                'title' => 'Printer tidak bisa print',
                'description' => 'Printer HP di ruang TU tidak bisa print. Di komputer terlihat printer online tapi saat dikirim dokumen tidak keluar.',
                'category' => 'hardware',
                'priority' => 'low',
                'status' => 'closed',
                'resolved_at' => now()->subDays(3),
                'closed_at' => now()->subDays(2),
            ],
        ];

        foreach ($tickets as $ticketData) {
            Ticket::create($ticketData);
        }
    }
}
