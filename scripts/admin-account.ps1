param(
    [ValidateSet('create', 'reset')] [string] $Action = 'create',
    [string] $PhpBinary = 'php'
)

$ErrorActionPreference = 'Stop'
$OutputEncoding = [System.Text.UTF8Encoding]::new($false)
Set-Location -LiteralPath (Split-Path -Parent $PSScriptRoot)
if ($Action -eq 'create') {
    $name = Read-Host 'Name'
    $email = Read-Host 'Email'
    $role = Read-Host 'Role [super_admin/editor] (default super_admin)'
} else {
    $email = Read-Host 'Existing account email'
}
try {
    $secret = Read-Host 'Password (10 to 72 bytes)' -AsSecureString
    $confirmationSecret = Read-Host 'Confirm password' -AsSecureString
    $password = [System.Net.NetworkCredential]::new('', $secret).Password
    $confirmation = [System.Net.NetworkCredential]::new('', $confirmationSecret).Password
    if ($password -cne $confirmation) { throw 'Passwords do not match.' }
    $bytes = [System.Text.Encoding]::UTF8.GetByteCount($password)
    if ($bytes -lt 10 -or $bytes -gt 72 -or $password.Contains("`0")) { throw 'Password must contain 10 to 72 bytes and no null characters.' }
    if ($Action -eq 'create') {
        @($name, $email, $password, $role) | & $PhpBinary 'scripts/create-admin.php'
    } else {
        @($email, $password) | & $PhpBinary 'scripts/reset-admin-password.php'
    }
    $result = $LASTEXITCODE
} finally {
    Remove-Variable password, confirmation, secret, confirmationSecret -ErrorAction SilentlyContinue
}
exit $result
