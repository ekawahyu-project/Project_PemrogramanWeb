<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Susun payload notifikasi SweetAlert untuk daftar pesan error validasi.
     *
     * @param  array<int, string>  $errorsList
     * @return array<string, string>
     */
    protected function buildValidationErrorNotif(array $errorsList): array
    {
        $daftar = implode('', array_map(
            fn (string $e): string => '<li style="margin-bottom:4px">• '.e($e).'</li>',
            $errorsList
        ));

        return [
            'icon' => 'error',
            'title' => 'Gagal!',
            'html' => '<ul style="text-align:left;list-style:none;padding:0;font-size:14px">'.$daftar.'</ul>',
            'confirmButtonText' => 'Coba lagi',
        ];
    }
}
