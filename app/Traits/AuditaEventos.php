<?php

namespace App\Traits;

use App\Services\EventoSistemaService;

trait AuditaEventos
{
    protected static function bootAuditaEventos()
    {
        /*
        |----------------------------------------------------------
        | ➕ CREATE
        |----------------------------------------------------------
        */
        static::created(function ($model) {

            EventoSistemaService::registrar(
                'CREATE_'.strtoupper(class_basename($model)),
                self::getModulo($model),
                $model->getKey(),
                null,
                $model->toArray()
            );
        });

        /*
        |----------------------------------------------------------
        | ✏️ UPDATE
        |----------------------------------------------------------
        */
        static::updating(function ($model) {

            $before = $model->getOriginal();

            EventoSistemaService::registrar(
                'UPDATE_'.strtoupper(class_basename($model)),
                self::getModulo($model),
                $model->getKey(),
                $before,
                null
            );
        });

        static::updated(function ($model) {

            EventoSistemaService::registrar(
                'UPDATED_'.strtoupper(class_basename($model)),
                self::getModulo($model),
                $model->getKey(),
                null,
                $model->fresh()->toArray()
            );
        });

        /*
        |----------------------------------------------------------
        | 🗑️ DELETE
        |----------------------------------------------------------
        */
        static::deleted(function ($model) {

            EventoSistemaService::registrar(
                'DELETE_'.strtoupper(class_basename($model)),
                self::getModulo($model),
                $model->getKey(),
                $model->toArray(),
                null
            );
        });
    }

    /*
    |----------------------------------------------------------
    | 🧠 IDENTIFICA MÓDULO AUTOMATICAMENTE
    |----------------------------------------------------------
    */
    private static function getModulo($model): string
    {
        return strtolower(class_basename($model));
    }
}
