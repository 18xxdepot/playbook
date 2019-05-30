pnp.18xxdepot.com {
    jwt {
        path /
        publickey /etc/login/login.public.pem
        redirect https://login.18xxdepot.com/?redirect=https%3A%2F%2Fpnp.18xxdepot.com{uri_escaped}
    }

    proxy / localhost:8082 {
        header_downstream Cache-Control "no-cache"
    }
}
