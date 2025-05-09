<?php

namespace Structs\Notificaciones;

abstract class EstructuraNotificaciones
{
    abstract public function ConteoNotificacionesUsuario();
    abstract public function NotificacionesUsuario();
    abstract public function NotificacionesMarcarLeidas();
}