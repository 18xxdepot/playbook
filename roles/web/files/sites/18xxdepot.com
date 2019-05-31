18xxdepot.com {
    jwt {
        path /
        publickey /etc/login/login.public.pem
        passthrough
    }

    proxy / localhost:8080 {
        transparent
    }
}
