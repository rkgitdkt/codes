/*Step 1 : create a middleware : ValidateWebSubdomain.php & write logic to validate for particular domain*/
<?php
namespace App\Http\Middleware;
use Redirect;
use Request;
use Closure;
class ValidateWebSubdomain 
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) 
    {
        $sub_domain = explode('.', $_SERVER['HTTP_HOST']);
        if($sub_domain[0] == 'integrity')
        {
            header('Location: https://www.padhaikaro.com'.$_SERVER['REQUEST_URI']);
            exit;
        }
        return $next($request);
    }
}

/* Step 2 : register it in 'web' group middle ware.*/

'web' => [
    \App\Http\Middleware\ValidateWebSubdomain::class,
    \App\Http\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    // \Illuminate\Session\Middleware\AuthenticateSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \App\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],

/*That's it.*/
