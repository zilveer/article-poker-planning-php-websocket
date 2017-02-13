(function ($, jsonEncode, autobahn) {
    'use strict';

    var apiUrl = 'http://localhost:20000/index-dev.php/api';
    var websocketUrl = 'ws://localhost:20002';
    var autobahnSession = null;

    autobahn.connect(websocketUrl, onOpen, onError);

    function onOpen(session) {
        autobahnSession = session;

        autobahnSession.subscribe('teams', function (topic, event) {
            console.log('msg teams', topic, event);

            switch (event.type) {
                case 'team_created':
                    App.Model.teams.push(event.team);
                    App.Teams.View.refresh();
                    break;
            }
        });
    }

    function onError(code, reason, detail) {
        console.warn('error', code, reason, detail);
    }

    var App = {};

    App.Model = {
        user: {
            pseudo: ''
        },
        teams: [],
        team: null
    };

    App.User = {};

    App.User.View = {
        init: function () {
            $('.screen-pseudo form [type=submit]').click(function (e) {
                App.User.Controllers.selectPseudo($('#input-pseudo').val());

                e.preventDefault();
                return false;
            });
        },

        refresh: function () {
        }
    };

    App.User.Controllers = {
        selectPseudo: function (pseudo) {
            $.post(apiUrl+'/users', jsonEncode({pseudo: pseudo})).then(function (user) {
                App.Model.user = user;
                App.Teams.display();
            });
        }
    };

    App.Teams = {};

    App.Teams.View = {
        init: function () {
            App.Teams.Controllers.ajaxRefreshTeams();

            $('.screen-teams .refresh-teams').click(function (e) {
                App.Teams.Controllers.ajaxRefreshTeams();

                e.preventDefault();
                return false;
            });

            $('.screen-teams .create-team').click(function (e) {
                var teamTitle = prompt('Nom de l\'équipe');

                App.Teams.Controllers.createTeam(teamTitle).then(function (team) {
                    App.Teams.Controllers.selectTeam(team);
                });

                e.preventDefault();
                return false;
            });
        },

        refresh: function () {
            var $teamsContainer = $('.screen-teams .teams-list');

            $teamsContainer.empty();

            var $button = $('<button>')
                .attr('type', 'button')
                .addClass('btn btn-primary btn-lg btn-list')
            ;

            var $icon = $('<i>')
                .addClass('fa fa-users')
                .attr('aria-hidden', 'true')
            ;

            $button
                .append($icon)
            ;

            $.each(App.Model.teams, function (index, team) {
                $button
                    .clone()
                    .click(function (e) {
                        App.Teams.Controllers.selectTeam(team);

                        e.preventDefault();
                        return false;
                    })
                    .appendTo($teamsContainer)
                    .append(team.title)
                ;
            });
        }
    };

    App.Teams.Controllers = {
        selectTeam: function (team) {
            $.ajax({
                method: 'PUT',
                url: apiUrl+'/teams/'+team.id+'/users/'+App.Model.user.id
            }).then(function (team) {
                App.Model.team = team;
                App.Team.display();

                autobahnSession.subscribe('teams/'+team.id, function (topic, event) {
                    console.log('msg team', topic, event);

                    switch (event.type) {
                        case 'user_joined':
                            App.Model.team.users.push(event.user);
                            break;

                        case 'user_voted':
                            $.each(App.Model.team.users, function (index, user) {
                                if (user.id === event.user.id) {
                                    App.Model.team.users[index].vote = event.user.vote;
                                    App.Model.team.vote_in_progress = event.user.team.vote_in_progress;
                                    return false;
                                }
                            });
                            break;
                    }

                    App.Team.View.refresh();
                });
            });
        },

        createTeam: function (title) {
            return $.post(apiUrl+'/teams', jsonEncode({
                title: title
            }));
        },

        ajaxRefreshTeams: function () {
            $.get(apiUrl+'/teams').then(function (teams) {
                App.Model.teams = teams;
                App.Teams.View.refresh();
            });
        }
    };

    App.Team = {};

    App.Team.View = {
        init: function () {
            var $cardsContainer = $('.fibonacci-list');

            $cardsContainer.empty();

            var $button = $('<button>')
                .attr('type', 'button')
                .addClass('btn btn-primary btn-lg btn-list')
            ;

            $.each([1, 2, 3, 5, 8, 13, 21, 34], function (index, vote) {
                $button
                    .clone()
                    .click(function (e) {
                        App.Team.Controllers.vote(vote);

                        e.preventDefault();
                        return false;
                    })
                    .appendTo($cardsContainer)
                    .append(vote)
                ;
            });

            $('.screen-vote .refresh-team').click(function (e) {
                App.Team.Controllers.ajaxRefreshTeam();

                e.preventDefault();
                return false;
            });
        },

        refresh: function () {
            $('.screen-vote .team-name').html(App.Model.team.title);

            $('.screen-vote h2 .badge')
                .removeClass('badge-warning')
                .removeClass('badge-success')
                .addClass(App.Model.team.vote_in_progress ? 'badge-warning' : 'badge-succes')
                .html(App.Model.team.vote_in_progress ? 'Vote en cours...' : 'Vote terminé')
            ;

            var $teamUsersContainer = $('.team-users-list');

            $teamUsersContainer.empty();

            var $user = $('<li>')
                .addClass('list-group-item')
            ;

            $.each(App.Model.team.users.sort(by('pseudo')), function (index, user) {
                var $createdUser = $user
                    .clone()
                    .append(user.pseudo)
                ;

                var isMe = user.id === App.Model.user.id;

                if (isMe) {
                    $createdUser.addClass('me');
                }

                if (user.vote) {
                    if (!App.Model.team.vote_in_progress || isMe) {
                        $createdUser.append(' <span class="badge badge-primary">'+user.vote+'</span>');
                    } else {
                        $createdUser.append(' <span class="badge badge-default">a voté</span>');
                    }
                }

                $teamUsersContainer.append($createdUser);
            });
        }
    };

    App.Team.Controllers = {
        vote: function (vote) {
            $.post(apiUrl+'/users/'+App.Model.user.id+'/vote', String(vote));
            App.Model.user.vote = vote;
            $.each(App.Model.team.users, function (index, user) {
                if (App.Model.user.id === user.id) {
                    App.Model.team.users[index].vote = vote;
                    return false;
                }
            });
            App.Team.View.refresh();
        },

        ajaxRefreshTeam: function () {
            $.get(apiUrl+'/teams/'+App.Model.team.id).then(function (team) {
                App.Model.team = team;
                App.Team.View.refresh();
            });
        }
    };

    App.display = function (name) {
        $('.screen').hide();
        $('.screen-'+name).show();
    };

    App.User.display = function () {
        App.User.View.refresh();
        App.display('pseudo');
    };

    App.Teams.display = function () {
        App.Teams.View.refresh();
        App.display('teams');
    };

    App.Team.display = function () {
        App.Team.View.refresh();
        App.display('vote');
    };

    App.User.View.init();
    App.Teams.View.init();
    App.Team.View.init();

    App.User.display();

    function by(property) {
        return function (a, b) {
            return a[property] > b[property] ? 1 : -1;
        };
    }
})(jQuery, JSON.stringify, ab);
