<?php
namespace CPath\Handlers\Views\Routes;

use CPath\Handlers\Api\Interfaces\IAPI;
use CPath\Handlers\Views\APIView;
use CPath\Interfaces\IRequest;
use CPath\Route\InvalidHandlerException;
use CPath\Route\Route;

class APIViewRouteDELETE extends Route {

    /**
     * Renders the route destination using an IRequest instance
     * @param IRequest $Request the request to render
     * @return void
     * @throws InvalidHandlerException if the destination handler was invalid
     */
    public function renderDestination(IRequest $Request) {
        $API = $this->loadHandler();
        $View = new APIView($API);
        $View->render($Request);
    }

    // Static

    /**
     * @param IAPI $API
     * @return APIViewRoute
     */
    static function fromAPI(IAPI $API) {
        $Route = static::fromHandler($API);
        return $Route;
    }
}