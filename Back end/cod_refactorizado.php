/*
Nombre: post_confirm
Descripción:
1. Recibe dos parámetros por POST: El id de un servicio, el id de un conductor
2. Cambia el estado de varias entidades en la base de datos basado en la lógica del negocio.
3. Envía notificaciones y retorna una respuesta.
Parametros de entrada:
service_id = identificador del servicio
driver_id = identificador del conductor
Parametros de salida:
Mensaje de retorno en formato json.

*/
public function post_confirm()
{
    $id = Input::get('service_id');
    $servicio = Service::find($id);

    if(!is_null($servicio))
    {
        if(strcmp ($servicio->status_id , '6' ) == 0)
        {
            return  Response::json(array('error'=>2));
        }
        else if(is_null($servicio) && strcmp ($servicio->status_id , '6' ) == 0)
        {
            $servicio = Service::update($id,array(
                                            'driver_id'=>Input::get('driver_id'),
                                            'status_is'=>'2'
                                        ));

            Driver::update(Input::get('driver_id'),array(
                                                  'available'=>0
                                      ));

            $driverTmp = Driver::find(Input::get('driver_id'));

            Service::update($id,array(
                                'car_id'=>$driverTmp->car_id
                            ));

            $pushMessage = 'Tu servicio ha sido confirmado!';

            $servicio = Service::find($id);
            $push = Push::make();

            if(strcmp ($servicio->user->uuid , '' ) == 0)
            {
              return  Response::json(array('error'=>0));
            }
            if(strcmp ($servicio->user->uuid , '' ) == 0)
            {
                $result = $push->ios($servicio->user->uuid, $pushMessage, 1, 'honk.wav', 'Open', array('serviceId'=>$servicio_id));

                if($result) // se asume que la funcion retorna un booleano
                   return  Response::json(array('error'=>4)); // el error de valor 4 define el fallo de nevio del mensaje push
            }
            else
            {
                $result = $push->android2($servicio->user->uuid, $pushMessage, 1, 'default', 'Open', array('serviceId'=>$servicio_id));

                if($result)// se asume que la funcion retorna un booleano
                   return  Response::json(array('error'=>4)); // el error de valor 4 define el fallo de nevio del mensaje push
            }
            return  Response::json(array('error'=>0));
        }
        else
        {
            return  Response::json(array('error'=>1));
        }
    }
    else
    {
        return  Response::json(array('error'=>3));
    }
}
