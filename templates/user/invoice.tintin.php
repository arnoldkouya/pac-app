<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Facture</title>

		<style>
			.invoice-box {
				max-width: 800px;
				margin: auto;
				padding: 30px;
				border: 1px solid #eee;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
				font-size: 14px;
				line-height: 20px;
				font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
				color: #000;
			}

			.invoice-box table {
				width: 100%;
				line-height: inherit;
				text-align: left;
			}

			.invoice-box table td {
				padding: 5px;
				vertical-align: top;
			}

			.invoice-box table tr td:nth-child(2) {
				text-align: right;
			}

			.invoice-box table tr.top table td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.top table td.title {
				font-size: 45px;
				line-height: 45px;
				color: #333;
			}

			.invoice-box table tr.information table td {
				padding-bottom: 40px;
			}

			.invoice-box table tr.heading td {
				background: #eee;
				border-bottom: 1px solid #ddd;
				font-weight: bold;
			}

			.invoice-box table tr.details td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.item td {
				border-bottom: 1px solid #eee;
			}

			.invoice-box table tr.item.last td {
				border-bottom: none;
			}

			.invoice-box table tr.total td:nth-child(2) {
				border-top: 2px solid #eee;
				font-weight: bold;
			}

			%media only screen and (max-width: 600px) {
				.invoice-box table tr.top table td {
					width: 100%;
					display: block;
					text-align: center;
				}

				.invoice-box table tr.information table td {
					width: 100%;
					display: block;
					text-align: center;
				}
			}

			/** RTL **/
			.invoice-box.rtl {
				direction: rtl;
				font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
			}

			.invoice-box.rtl table {
				text-align: right;
			}

			.invoice-box.rtl table tr td:nth-child(2) {
				text-align: left;
			}
		</style>
	</head>

	<body>
		<div class="invoice-box">
			<table cellpadding="0" cellspacing="0">
				<tr class="top">
					<td colspan="2">
						<table>
							<tr>
								<td class="title">
									<img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path("/img/brand/logo.png"))) }}" style="width: 100%; max-width: 100px" />
								</td>
								<td>
									Facture #: {{ $invoice_number }}<br />
									Généré le: {{ date('d/m/Y H:i:s') }}<br />
									Émis: {{ $transaction->created_at->format('d/m/Y H:i:s') }}
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="information">
					<td colspan="2">
						<table>
							<tr>
								<td>
									Papac & Co <sub>by papacandco Côte d'Ivoire</sub>.<br />
									Riviera Palmeraie<br />
									hello%papacandcohq.com / +225 07 49 92 95 98
								</td>
								<td>
									{{ $transaction->user->name }}<br />
									{{ $transaction->user->isPremium() ? 'Membre premium' : 'Membre free' }}<br />
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="heading">
					<td>Methode de paiement</td>
					<td>#</td>
				</tr>

				<tr class="details">
					<td>{{ $transaction->provider }}</td>
					<td>{{ $transaction->amount }} {{ $transaction->extras->currency }}</td>
				</tr>

				<tr class="heading">
					<td>Produit</td>
					<td>Prix</td>
				</tr>

				<tr class="item">
					<td>{{ $transaction->label }}</td>
					<td>{{ $transaction->amount }} {{ $transaction->extras->currency }}</td>
				</tr>

				<tr class="total">
					<td></td>
					<td>Total: {{ $transaction->amount }} {{ $transaction->extras->currency }}</td>
				</tr>
			</table>
		</div>
	</body>
</html>
