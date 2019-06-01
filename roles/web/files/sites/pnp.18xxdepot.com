pnp.18xxdepot.com {
    import auth-strict

    proxy / localhost:8082 {
        header_downstream Cache-Control "no-cache"
    }
}
