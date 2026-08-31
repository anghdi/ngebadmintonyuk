<?php

if (! function_exists('rupiah')) {
    function rupiah(int|float|null $value): string
    {
        $value ??= 0;

        return ($value < 0 ? '- ' : '').'Rp'.number_format(abs($value), 0, ',', '.');
    }
}
if (! function_exists('signed_rupiah')) {
    function signed_rupiah(int|float|null $value): string
    {
        return ($value > 0 ? '+ ' : ($value < 0 ? '- ' : '')).'Rp'.number_format(abs($value ?? 0), 0, ',', '.');
    }
}
