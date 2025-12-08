<?php

return [
     'error' => 'Error', // You can also use 'Error' in Spanish
    'description' => 'Descripción',
    'resolution' => 'Resolución',
    
    'ERR001' => [
        'message' => '¡Por favor seleccione un mostrador primero!',
        'description' => 'El usuario intentó una acción sin seleccionar un mostrador.',
        'resolution' => 'Indique al usuario que elija un mostrador antes de continuar.',
    ],
    'ERR002' => [
        'message' => 'Sin llamada',
        'description' => 'No hay una llamada activa para realizar la operación.',
        'resolution' => 'Inicie o espere una nueva llamada.',
    ],
    'ERR003' => [
        'message' => '¡Cierre primero la llamada en curso!',
        'description' => 'El usuario debe finalizar la llamada activa antes de iniciar una nueva.',
        'resolution' => 'Cierre la llamada actual antes de continuar.',
    ],
    'ERR004' => [
        'message' => 'Esta llamada está en espera temporalmente',
        'description' => 'La llamada seleccionada está en espera temporal.',
        'resolution' => 'Reanude la llamada antes de continuar.',
    ],
    'ERR005' => [
        'message' => 'Esta llamada está en espera temporal (inicio)',
        'description' => 'La llamada en cola ha entrado en un estado de espera temporal.',
        'resolution' => 'Espere hasta que se elimine la espera o reanude manualmente.',
    ],
    'ERR006' => [
        'message' => 'El número de cola ya existe',
        'description' => 'El número de cola ya existe en el sistema.',
        'resolution' => 'Genere un nuevo número de cola único.',
    ],
    'ERR007' => [
        'message' => '¡El sistema no tiene una llamada que esté siendo atendida por usted!',
        'description' => 'No se encontró ninguna llamada activa asignada al usuario.',
        'resolution' => 'Asegúrese de que se esté atendiendo una llamada.',
    ],
    'ERR008' => [
    'message' => "El número de turno ya existe",
    'description' => 'El número de turno ya existe en la base de datos',
    'resolution' => 'Por favor, verifique su número de turno',
    ],
    'ERR009' => [
        'message' => "¡La cola actual ha sido reiniciada y no estaba cerrada!",
        'description' => 'La cola actual ha sido reiniciada',
        'resolution' => 'Su turno ha sido reiniciado',
    ],

    'BOOK001' => [
        'message' => 'No se puede generar el ticket debido a reglas inválidas.',
        'description' => 'La configuración del sistema bloqueó la creación del ticket.',
        'resolution' => 'Contacte al administrador para revisar las reglas de reserva.',
    ],
    'BOOK002' => [
        'message' => 'Pago fallido: Algo salió mal',
        'description' => 'Ocurrió un problema desconocido durante el pago.',
        'resolution' => 'Intente el pago nuevamente o contacte al soporte.',
    ],
    'BOOK003' => [
        'message' => 'Faltan claves del servicio de pago',
        'description' => 'No se han configurado las credenciales de API para el pago.',
        'resolution' => 'Configure la clave y el secreto API en la configuración.',
    ],
    'BOOK004' => [
        'message' => 'La configuración de pago no está configurada',
        'description' => 'La configuración de pago está incompleta.',
        'resolution' => 'Complete la configuración de pago en el panel de administración.',
    ],
    
    'SUCCESS001' => [
        'message' => 'Llamada realizada con éxito',
    ],
    'SUCCESS002' => [
        'message' => 'Suspensión procesada correctamente con notificaciones enviadas',
    ],
    'SUCCESS003' => [
        'message' => 'Llamada iniciada con éxito'
    ],
    'SUCCESS004' => [
        'message' => 'Llamada finalizada con éxito'
    ],
    'SUCCESS005' => [
        'message' => 'Transferencia de llamada realizada con éxito'
    ],
    'SUCCESS006' => [
        'message' => 'Llamada recordada con éxito'
    ],
    'SUCCESS007' => [
        'message' => 'Llamada devuelta con éxito'
    ],
    'SUCCESS008' => [
        'message' => 'La solicitud ha sido enviada al administrador'
    ],
    'SUCCESS009' => [
        'message' => 'Puesta en espera realizada con éxito'
    ],
    'SUCCESS0010' => [
        'message' => 'Cancelación realizada con éxito'
    ],
    'SUCCESS0011' => [
        'message' => '¡SMS enviado con éxito!'
    ],
    'SUCCESS0012' => [
        'message' => 'Cola generada con éxito'
    ],
    'SUCCESS0013' => [
        'message' => 'Nota de estimación actualizada con éxito'
    ],
    'SUCCESS0014' => [
        'message' => 'Reversión de llamada realizada con éxito'
    ],
    'SUCCESS0015' => [
        'message' => 'Visitante editado con éxito'
    ],
    'SUCCESS0016' => [
        'message' => 'Llamada perdida con éxito!'
    ],
   'SUCCESS0017' => [
    'message' => 'Datos guardados correctamente'
],


    'VAL001' => [
        'message' => 'Por favor ingrese el número de cola y la categoría',
    ],
    'VAL002' => [
        'message' => 'Por favor ingrese el tipo de pausa y un comentario',
    ],


    'Click on the continue button to unlock this screen! Break time is for' => 'Haz clic en el botón continuar para desbloquear esta pantalla. El tiempo de pausa es de',
    'minutes.' => 'minutos.',
    'CONTINUE' => 'CONTINUAR',
    'Call started Successfully' => 'Llamada iniciada con éxito',
    'success' => 'éxito',
    'Suspension processed successfully with notifications sent' => 'Suspensión procesada correctamente con notificaciones enviadas',
    'Are you sure' => '¿Estás seguro?',
    'warning' => 'advertencia',
    'You want to revert this' => '¿Quieres revertir esto?',
    'YES, REVERT IT' => 'SÍ, REVERTIR',
    'No, CANCEL' => 'No, CANCELAR',
    'Please rate our service' => 'Por favor califica nuestro servicio',
    'Excellent' => 'Excelente',
    'Good' => 'Bueno',
    'Neutral' => 'Neutral',
    'Poor' => 'Malo',
    'Please Wait' => 'Por favor espera',
    'Revert Queue' => 'Revertir cola',
    'Cancelled' => 'Cancelado',
    'Your data is safe' => 'Tus datos están seguros',
    'error' => 'error',
    "You won't be able to revert this" => "¡No podrás revertir esto!",
    'OK' => 'OK',
    'Cancel' => 'Cancelar',
    'Please enter queue number and category' => 'Por favor ingrese el número de cola y la categoría',
    'Break' => 'Pausa',
    'Choose Any Reason' => 'Elige una razón',
    'Comment' => 'Comentario',
    'Please enter break type and comment' => 'Por favor ingrese el tipo de pausa y un comentario',
    'Enter Queue Number' => 'Ingrese el número de cola',
    'Select Category' => 'Seleccionar categoría',
    'Type of Break' => 'Tipo de descanso',
    'Unlock Screen' => 'Desbloquear pantalla',
    'Updating' => 'Actualizando',
    'Success!' => '¡Éxito!',
    'Yes, delete it' => 'Sí, eliminarlo',
    'Data Deleted Successfully' => 'Datos eliminados correctamente',
    'No record selected' => 'Ningún registro seleccionado',

];
