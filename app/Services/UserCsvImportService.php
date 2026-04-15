<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserCsvImportService
{
    /**
     * Expected header row (case-insensitive). is_admin optional (0/1/true/false/empty).
     *
     * @return array{created: int, errors: list<array{line: int, message: string}>}
     */
    public function import(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => [__('Could not read the uploaded file.')],
            ]);
        }

        $headerLine = fgetcsv($handle);
        if ($headerLine === false) {
            fclose($handle);

            throw ValidationException::withMessages([
                'file' => [__('The CSV file is empty.')],
            ]);
        }

        $headers = array_map(function (string $h): string {
            $h = strtolower(trim($h));

            return preg_replace('/^\xEF\xBB\xBF/', '', $h) ?? $h;
        }, $headerLine);

        $expected = [
            'name', 'email', 'phone', 'nid_number', 'address',
            'nominee_name', 'nominee_email', 'nominee_phone', 'nominee_address',
            'password',
        ];

        foreach ($expected as $col) {
            if (! in_array($col, $headers, true)) {
                fclose($handle);
                throw ValidationException::withMessages([
                    'file' => [__('CSV must include a header row with columns: :cols', ['cols' => implode(', ', array_merge($expected, ['is_admin (optional)']))])],
                ]);
            }
        }

        $indexes = array_flip($headers);
        $created = 0;
        $errors = [];
        $lineNo = 1;

        $headerCount = count($headers);

        while (($row = fgetcsv($handle)) !== false) {
            $lineNo++;
            if ($this->rowIsBlank($row)) {
                continue;
            }

            while (count($row) < $headerCount) {
                $row[] = '';
            }

            $parsed = $this->rowToAssoc($row, $indexes);

            $isAdmin = $parsed['is_admin'];
            unset($parsed['is_admin']);

            $validator = Validator::make($parsed, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'phone' => ['required', 'string', 'max:32'],
                'nid_number' => ['required', 'string', 'max:64', 'unique:users,nid_number'],
                'address' => ['required', 'string', 'max:2000'],
                'nominee_name' => ['required', 'string', 'max:255'],
                'nominee_email' => ['required', 'email', 'max:255'],
                'nominee_phone' => ['required', 'string', 'max:32'],
                'nominee_address' => ['required', 'string', 'max:2000'],
                'password' => ['required', Password::defaults()],
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'line' => $lineNo,
                    'message' => $validator->errors()->first(),
                ];

                continue;
            }

            $clean = $validator->validated();

            try {
                DB::transaction(function () use ($clean, $isAdmin): void {
                    $user = User::create([
                        'name' => $clean['name'],
                        'email' => $clean['email'],
                        'phone' => $clean['phone'],
                        'nid_number' => $clean['nid_number'],
                        'address' => $clean['address'],
                        'password' => Hash::make($clean['password']),
                        'role' => User::ROLE_INVESTOR,
                        'is_admin' => $isAdmin,
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]);

                    $user->nominee()->create([
                        'name' => $clean['nominee_name'],
                        'email' => $clean['nominee_email'],
                        'phone' => $clean['nominee_phone'],
                        'address' => $clean['nominee_address'],
                    ]);
                });
                $created++;
            } catch (\Throwable $e) {
                $errors[] = [
                    'line' => $lineNo,
                    'message' => __('Could not save this row.'),
                ];
            }
        }

        fclose($handle);

        return ['created' => $created, 'errors' => $errors];
    }

    public function csvTemplateHeaders(): string
    {
        return implode(',', [
            'name',
            'email',
            'phone',
            'nid_number',
            'address',
            'nominee_name',
            'nominee_email',
            'nominee_phone',
            'nominee_address',
            'password',
            'is_admin',
        ])."\n";
    }

    /**
     * @param  list<string|null>  $row
     */
    private function rowIsBlank(array $row): bool
    {
        foreach ($row as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string|null>  $row
     * @param  array<string, int>  $indexes
     * @return array<string, string|bool>
     */
    private function rowToAssoc(array $row, array $indexes): array
    {
        $pick = function (string $key) use ($row, $indexes): string {
            $i = $indexes[$key] ?? null;
            if ($i === null) {
                return '';
            }

            return trim((string) ($row[$i] ?? ''));
        };

        $isAdminRaw = array_key_exists('is_admin', $indexes)
            ? strtolower(trim((string) ($row[$indexes['is_admin']] ?? '')))
            : '';

        $isAdmin = in_array($isAdminRaw, ['1', 'true', 'yes'], true);

        return [
            'name' => $pick('name'),
            'email' => $pick('email'),
            'phone' => $pick('phone'),
            'nid_number' => $pick('nid_number'),
            'address' => $pick('address'),
            'nominee_name' => $pick('nominee_name'),
            'nominee_email' => $pick('nominee_email'),
            'nominee_phone' => $pick('nominee_phone'),
            'nominee_address' => $pick('nominee_address'),
            'password' => $pick('password'),
            'is_admin' => $isAdmin,
        ];
    }
}

