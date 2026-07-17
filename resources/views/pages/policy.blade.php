@php
    $routeName = request()->route()?->getName();

    $policies = [
        'refund-policy' => [
            'title' => 'Returns policy',
            'description' => 'Bloxo returns and refunds information.',
            'intro' => 'We have a 14-day return policy, which means you have 14 days after receiving your item to request a return.',
            'sections' => [
                [
                    'heading' => 'Eligibility',
                    'body' => [
                        'To be eligible for a return, your item must be in the same condition that you received it, unworn or unused, and in its original packaging. You will also need the receipt or proof of purchase.',
                        'To start a return, you can contact us at mikeprior2001@gmail.com. Please note that returns will need to be sent to the following address: 6 Chichester Way, Selsey, PO20 0PJ.',
                        'If your return is accepted, we will send you instructions on how and where to send your package. Items sent back to us without first requesting a return will not be accepted.',
                        'You can always contact us for any return questions at mikeprior2001@gmail.com.',
                    ],
                ],
                [
                    'heading' => 'Damages and issues',
                    'body' => [
                        'Please inspect your order upon reception and contact us immediately if the item is defective, damaged, or if you receive the wrong item so that we can evaluate the issue and make it right.',
                    ],
                ],
                [
                    'heading' => 'Exceptions and non-returnable items',
                    'body' => [
                        'Unfortunately, we cannot accept returns on sale items or gift cards.',
                    ],
                ],
                [
                    'heading' => 'European Union 14 day cooling off period',
                    'body' => [
                        'Notwithstanding the above, if the merchandise is being shipped into the European Union, you have the right to cancel or return your order within 14 days, for any reason and without a justification. As above, your item must be in the same condition that you received it, unused, and in its original packaging. You will also need the receipt or proof of purchase.',
                    ],
                ],
                [
                    'heading' => 'Refunds',
                    'body' => [
                        'We will notify you once we have received and inspected your return, and let you know if the refund was approved or not. If approved, you will be automatically refunded on your original payment method within 10 business days. Please remember it can take some time for your bank or credit card company to process and post the refund too.',
                        'If more than 15 business days have passed since we approved your return, please contact us at mikeprior2001@gmail.com.',
                    ],
                ],
            ],
        ],
        'privacy-policy' => [
            'title' => 'Privacy policy',
            'description' => 'How Bloxo handles personal information.',
            'intro' => 'This Privacy Policy explains how Bloxo collects, uses and protects personal information when you use our website, online game calculator, Bloxo app, or contact us.',
            'sections' => [
                [
                    'heading' => 'Information we collect',
                    'body' => [
                        'We may collect information you provide directly, such as your name, email address, account details, support messages, and any information you send when contacting us.',
                        'If you use the Bloxo app or online features, we may process account information, game activity, saved games, gameplay records, purchase status, device information and technical logs needed to run and improve the service.',
                        'If you buy Bloxo through Amazon, your purchase is handled by Amazon. We do not receive or store your full payment card details from Amazon.',
                    ],
                ],
                [
                    'heading' => 'How we use information',
                    'body' => [
                        'We use personal information to provide and improve Bloxo, manage accounts, support saved games and multiplayer features, respond to enquiries, maintain security, detect misuse, and comply with legal obligations.',
                        'We may use limited technical data to understand how the website and app are used, diagnose issues, and improve performance.',
                    ],
                ],
                [
                    'heading' => 'Payments and app purchases',
                    'body' => [
                        'In-app purchases are processed by Apple. Apple handles payment processing and may provide us with confirmation of purchase status so we can unlock access in the app.',
                    ],
                ],
                [
                    'heading' => 'Cookies and similar technologies',
                    'body' => [
                        'Our website may use cookies or similar technologies that are necessary for the website to work, remember preferences, protect forms, measure performance, and understand basic usage.',
                        'You can control cookies through your browser settings, although blocking some cookies may affect how parts of the website work.',
                    ],
                ],
                [
                    'heading' => 'Sharing information',
                    'body' => [
                        'We may share information with trusted service providers who help us operate the website, app, hosting, analytics, email, payments, customer support and security. These providers may only use the information to provide services to us.',
                        'We may also share information if required by law, to protect our rights, or to investigate misuse of our services.',
                    ],
                ],
                [
                    'heading' => 'Data retention and security',
                    'body' => [
                        'We keep personal information only for as long as needed to provide the service, meet legal obligations, resolve disputes, and maintain security. We use reasonable technical and organisational measures to protect information, but no online service can be guaranteed to be completely secure.',
                    ],
                ],
                [
                    'heading' => 'Your rights',
                    'body' => [
                        'Depending on where you live, you may have rights to access, correct, delete, restrict or object to the use of your personal information. You may also have the right to withdraw consent where processing is based on consent.',
                        'To exercise these rights, contact us at mikeprior2001@gmail.com.',
                    ],
                ],
                [
                    'heading' => 'Children',
                    'body' => [
                        'Bloxo is a family game, but our online services are not intended to collect personal information from children without appropriate consent. If you believe a child has provided us with personal information, please contact us and we will take appropriate steps.',
                    ],
                ],
                [
                    'heading' => 'Contact',
                    'body' => [
                        'If you have questions about this Privacy Policy or how we handle personal information, email mikeprior2001@gmail.com or write to Bloxo, 6 Chichester Way, Selsey, Chichester PO20 0PJ, United Kingdom.',
                    ],
                ],
            ],
        ],
        'terms-of-service' => [
            'title' => 'Terms of service',
            'description' => 'Terms for using the Bloxo website and services.',
            'intro' => 'These Terms of Service apply when you access or use the Bloxo website, online game calculator, app, and related services.',
            'sections' => [
                [
                    'heading' => 'About Bloxo',
                    'body' => [
                        'This website is operated by Bloxo. Throughout the site, the terms we, us and our refer to Bloxo.',
                        'The website provides information about Bloxo, links to buy the physical game from Amazon, access to our online game calculator, and information about the Bloxo app.',
                    ],
                ],
                [
                    'heading' => 'Using the website and services',
                    'body' => [
                        'You agree to use the website and services lawfully and not to misuse, copy, interfere with, damage, reverse engineer, or attempt to gain unauthorised access to any part of the website, app, servers or systems.',
                        'We may update, suspend or withdraw parts of the website or services at any time.',
                    ],
                ],
                [
                    'heading' => 'Buying Bloxo',
                    'body' => [
                        'Bloxo may be purchased through third-party retailers such as Amazon. Purchases made through Amazon are subject to Amazon’s checkout, delivery, payment and marketplace terms.',
                        'Product availability, prices and delivery information may change without notice.',
                    ],
                ],
                [
                    'heading' => 'Accounts and app features',
                    'body' => [
                        'Some Bloxo app features may require an account. You are responsible for keeping your login details secure and for activity on your account.',
                        'App features, including multiplayer games, saved games, free allowances and paid unlocks, may change as we improve the service.',
                    ],
                ],
                [
                    'heading' => 'Intellectual property',
                    'body' => [
                        'Bloxo, the Bloxo name, game design, artwork, website content, app content and related materials are owned by us or our licensors. You may not reproduce or exploit them without permission, except as allowed by law.',
                    ],
                ],
                [
                    'heading' => 'Third-party links',
                    'body' => [
                        'Our website may link to third-party websites, including Amazon and app stores. We are not responsible for the content, terms, privacy practices or services of third-party websites.',
                    ],
                ],
                [
                    'heading' => 'Errors and availability',
                    'body' => [
                        'We try to keep information accurate, but we do not guarantee that the website, app or services will always be uninterrupted, error-free or completely up to date. We may correct errors or update content at any time.',
                    ],
                ],
                [
                    'heading' => 'Liability',
                    'body' => [
                        'Nothing in these terms limits any liability that cannot be limited by law. To the fullest extent permitted by law, we are not liable for indirect, incidental or consequential losses arising from use of the website, app or services.',
                    ],
                ],
                [
                    'heading' => 'Governing law',
                    'body' => [
                        'These terms are governed by the laws of England and Wales.',
                    ],
                ],
                [
                    'heading' => 'Contact',
                    'body' => [
                        'Questions about these Terms of Service should be sent to mikeprior2001@gmail.com.',
                    ],
                ],
            ],
        ],
        'contact-information' => [
            'title' => 'Contact information',
            'description' => 'Contact details for Bloxo.',
            'intro' => 'You can contact Bloxo using the details below.',
            'sections' => [
                [
                    'heading' => 'Bloxo',
                    'body' => [
                        'Trade name: Bloxo',
                        'Email: mikeprior2001@gmail.com',
                        'Physical address: 6 Chichester Way, Selsey, Chichester PO20 0PJ, United Kingdom',
                    ],
                ],
            ],
        ],
        'cookie-preferences' => [
            'title' => 'Cookie preferences',
            'description' => 'Cookie information for Bloxo.',
            'intro' => 'Bloxo uses cookies and similar technologies where needed to run the website, protect forms, remember preferences and understand basic website performance.',
            'sections' => [
                [
                    'heading' => 'Managing cookies',
                    'body' => [
                        'You can manage or block cookies through your browser settings. Blocking some cookies may affect how parts of the website work.',
                        'For privacy questions, contact mikeprior2001@gmail.com.',
                    ],
                ],
            ],
        ],
    ];

    $policy = $policies[$routeName] ?? [
        'title' => $title,
        'description' => 'Bloxo policy information.',
        'intro' => null,
        'sections' => [],
    ];
@endphp

<x-layouts.marketing
    :title="$policy['title'] . ' | Bloxo'"
    :description="$policy['description']"
>
    <main class="marketing-page">
        <section class="marketing-page-content policy-page-content">
            <p class="marketing-overline">Bloxo</p>
            <h1>{{ $policy['title'] }}</h1>

            @if ($policy['intro'])
                <p class="policy-intro">{{ $policy['intro'] }}</p>
            @endif

            <div class="policy-section-list">
                @foreach ($policy['sections'] as $section)
                    <section class="policy-section">
                        <h2>{{ $section['heading'] }}</h2>

                        @foreach ($section['body'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </section>
                @endforeach
            </div>
        </section>
    </main>

    <x-marketing.footer />
</x-layouts.marketing>
