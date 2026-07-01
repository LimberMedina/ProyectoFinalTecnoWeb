<?php

namespace App\Traits;

use App\Models\Bitacora;

/**
 * Trait para registrar automáticamente eventos en la bitácora
 * 
 * Uso en modelos Eloquent:
 * use RegistraBitacora;
 * 
 * Los eventos se registran automáticamente:
 * - created: cuando se crea un registro
 * - updated: cuando se actualiza un registro
 * - deleted: cuando se elimina un registro
 */
trait RegistraBitacora
{
    protected static function booted()
    {
        static::created(function ($model) {
            Bitacora::registrar(
                'create',
                class_basename($model),
                $model->id,
                $model->obtenerNombreEntidad(),
                null,
                "Se creó un nuevo registro de " . class_basename($model)
            );
        });

        static::updated(function ($model) {
            $cambios = [];
            
            // Detectar cambios
            foreach ($model->getChanges() as $campo => $valor) {
                $cambios[$campo] = [
                    'anterior' => $model->getOriginal($campo),
                    'nuevo' => $valor,
                ];
            }

            Bitacora::registrar(
                'update',
                class_basename($model),
                $model->id,
                $model->obtenerNombreEntidad(),
                $cambios,
                "Se actualizó un registro de " . class_basename($model)
            );
        });

        static::deleted(function ($model) {
            Bitacora::registrar(
                'delete',
                class_basename($model),
                $model->id,
                $model->obtenerNombreEntidad(),
                $model->toArray(),
                "Se eliminó un registro de " . class_basename($model)
            );
        });
    }

    /**
     * Método que los modelos pueden sobrescribir para personalizar el nombre de la entidad
     */
    public function obtenerNombreEntidad()
    {
        // Por defecto, intenta obtener el atributo 'nombre' o 'name'
        if (isset($this->nombre)) {
            return $this->nombre;
        }
        if (isset($this->name)) {
            return $this->name;
        }
        if (isset($this->title)) {
            return $this->title;
        }
        return "Registro #{$this->id}";
    }
}
