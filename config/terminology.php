<?php

// ✅ Definir términos estándar en toda la app
return [
    'actions' => [
        'create' => 'Crear',      // NO mezclar con "Agregar", "Nuevo", "Añadir"
        'edit' => 'Editar',       // NO mezclar con "Modificar", "Cambiar"
        'delete' => 'Eliminar',   // NO mezclar con "Borrar", "Quitar"
        'save' => 'Guardar',      // NO mezclar con "Salvar", "Grabar"
        'cancel' => 'Cancelar',   // Siempre igual
    ],
    
    'status' => [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
        'pending' => 'Pendiente',
        'completed' => 'Completado',
    ]
];