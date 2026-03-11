<?php

return [
    'required'             => 'O campo :attribute é obrigatório.',
    'email'                => 'O campo :attribute deve ser um e-mail válido.',
    'unique'               => 'Este :attribute já está sendo utilizado.',
    'min'                  => [
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'max'                  => [
        'string' => 'O campo :attribute não pode ter mais de :max caracteres.',
    ],
    'confirmed'            => 'A confirmação de :attribute não confere.',
    'date'                 => 'O campo :attribute deve ser uma data válida.',
    'after_or_equal'       => 'O campo :attribute deve ser uma data igual ou posterior a :date.',
    'in'                   => 'O valor selecionado para :attribute é inválido.',

    'attributes' => [
        'name'                  => 'nome',
        'email'                 => 'e-mail',
        'password'              => 'senha',
        'destination'           => 'destino',
        'departure_date'        => 'data de ida',
        'return_date'           => 'data de volta',
        'status'                => 'status',
    ],
];
