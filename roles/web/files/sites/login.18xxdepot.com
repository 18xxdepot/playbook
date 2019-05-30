login.18xxdepot.com {
    jwt {
        path /headers
        passthrough
        publickey /etc/login/login.public.pem
    }

    proxy / localhost:8081
}
