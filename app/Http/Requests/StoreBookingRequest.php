<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only authenticated clients can book
        return auth()->check() && auth()->user()->isClient();
    }

    public function rules(): array
    {
        return [
            'service_id'     => ['required', 'exists:services,id'],
            'start_datetime' => ['required', 'date', 'after:now'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $service = Service::find($this->service_id);

            if (! $service) {
                return;
            }

            $start = new \DateTime($this->start_datetime);
            $end   = (clone $start)->modify('+1 hour'); // default duration

            if (! $service->isAvailableAt($start, $end)) {
                $validator->errors()->add(
                    'start_datetime',
                    'The selected service is not available at this time.'
                );
            }
        });
    }
}
