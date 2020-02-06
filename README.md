# moodle_datac

## 概略

従来の標準データベースモジュールでは、エクスポート時はユーザ詳細を吐き出すことが可能であるが、インポート時にはこの項目を正しく読み取ることが行われていない。このため、以下のような欠点が生じてしまう。

- インポート時はすべてインポートした教師によるデータとなってしまい、どのユーザが作成したデータか判断できない
- 教師権限によるデータであるため、基本的にすべて承認済のステータスとなってしまう

今回のプラグインでは、この欠点を回避するため、CSVに記載されたユーザ名の列を受け取り、そのユーザを著者としたデータになるようインポートするオプションを取り入れた。学生ロールの場合、承認ステータスはそのデータベースモジュールでのデフォルト設定に準拠する形となる。

また、これと合わせ、moodledataフォルダに配置したCSVファイルをスケジュールタスクによって読み込む機能を追加した。これにより、他システムでの学習履歴などについて、CSVを経由することでMoodleに取り込むことも可能となる。

※現状、Moodle 3.5 の対応

## 今後の予定

- MoodleMobileアプリへの対応
- 最新Moodleへの対応

Default database module can export the detail of user information such as username and lastname. However, these user details are not imported from CSV even if there are fields of them. This causes some problems such as:
- The imported data are connected to the imported user (such as teachers or managers). So we can’t figure out the original data author.
- Since the data is imported as the posts of teacher, all items are “approved” automatically.
This plugin resolved this problem. The column of username is imported and the items are connected to the correct users. Since the data is imported as posts of students, the approved status function correctly.

In addition to this, we added another feature to import CSV files from the moodledata folder with the schedule tasks. This enables to import some learning logs from another LMS via CSV file as the database items.
