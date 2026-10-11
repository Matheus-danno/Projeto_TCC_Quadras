<?php

namespace App\Enums;

enum TipoDocumento: string
{
    case DocumentoIdentidade = 'documento_identidade';
    case ComprovanteTitularidadePix = 'comprovante_titularidade_pix';
    case ComprovanteImovel = 'comprovante_imovel';

    public function label(): string
    {
        return match ($this) {
            self::DocumentoIdentidade => 'Documento de identidade',
            self::ComprovanteTitularidadePix => 'Comprovante de titularidade (Pix/conta)',
            self::ComprovanteImovel => 'Comprovante de uso do imóvel',
        };
    }
}
