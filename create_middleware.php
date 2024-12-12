<?php

$middlewareDirectory = __DIR__ . '/app/Http/Middleware/';

// List of the middleware files with their contents
$middlewareFiles = [
    'PreventRequestsDuringMaintenance.php' => '<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    // Default functionality already handled by the parent class.
}
',
    'TrimStrings.php' => '<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array
     */
    protected $except = [
        \'password\',
        \'password_confirmation\',
    ];
}
',
    'EncryptCookies.php' => '<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        //
    ];
}
',
    'VerifyCsrfToken.php' => '<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // Add any URIs you want to exclude here.
    ];
}
',
    'Authenticate.php' => '<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route(\'login\');
        }
    }
}
',
    'RedirectIfAuthenticated.php' => '<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(\'/home\'); // Update this with the desired redirect path.
            }
        }

        return $next($request);
    }
}
'
];

// Create the middleware directory if it doesn't exist
if (!is_dir($middlewareDirectory)) {
    mkdir($middlewareDirectory, 0777, true);
}

// Loop through each middleware and create the files
foreach ($middlewareFiles as $fileName => $content) {
    $filePath = $middlewareDirectory . $fileName;

    if (!file_exists($filePath)) {
        file_put_contents($filePath, $content);
        echo "Created: $filePath\n";
    } else {
        echo "File already exists: $filePath\n";
    }
}

echo "All middleware files created successfully!\n";
