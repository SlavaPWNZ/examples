<?

use ItemAdapters\ClientActions;
use ItemAdapters\Rules;
use Kwabs\Controller\APIItemController;
use Kwabs\PaytureProxy;

Class ApiPaytureController extends APIItemController
{
    public static $itemClass = PaytureProxy::class;

    public static function link()
    {
        $link = PaytureProxy::Add(App::getUser()->n_id);
        if ($link) {
            return self::response($link);
        }
        return self::error('Ошибка соединения с сервером оплаты. Повторите запрос позже.');
    }

    public static function get()
    {
        $cardList = PaytureProxy::GetList(App::getUser()->n_id);
        return self::response($cardList);
    }

    public static function delete()
    {
        $result = PaytureProxy::Remove(App::getUser()->n_id, $_REQUEST['card_id']);
        if ($result) {
            ClientActions::addAction(App::getUser()->n_id, 'remove_card');
            return self::response('Карта успешно удалена');
        }
        return self::error('Ошибка при удалении карты');
    }

    public static function create() // OFF
    {
        return parent::error404();
    }

    public static function update($id = null) // OFF
    {
        return parent::error404();
    }
}
